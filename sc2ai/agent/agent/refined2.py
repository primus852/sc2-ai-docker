import random
import math
import os
import datetime
import time

import numpy as np
import pandas as pd

from sqlalchemy import create_engine, Column
from sqlalchemy.types import Integer, DateTime, Float
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from sqlalchemy_utils import database_exists, create_database

from pysc2.agents import base_agent
from pysc2.lib import actions, features, units

# python3 -m pysc2.bin.agent  --map Simple64  --agent agent.refined2.SparseAgent  --agent_race terran  --max_agent_steps 0 --use_feature_units --norender

_NO_OP = actions.FUNCTIONS.no_op.id
_SELECT_POINT = actions.FUNCTIONS.select_point.id
_BUILD_SUPPLY_DEPOT = actions.FUNCTIONS.Build_SupplyDepot_screen.id
_BUILD_BARRACKS = actions.FUNCTIONS.Build_Barracks_screen.id
_TRAIN_MARINE = actions.FUNCTIONS.Train_Marine_quick.id
_SELECT_ARMY = actions.FUNCTIONS.select_army.id
_ATTACK_MINIMAP = actions.FUNCTIONS.Attack_minimap.id
_HARVEST_GATHER = actions.FUNCTIONS.Harvest_Gather_screen.id

_PLAYER_RELATIVE = features.SCREEN_FEATURES.player_relative.index
_UNIT_TYPE = features.SCREEN_FEATURES.unit_type.index
_PLAYER_ID = features.SCREEN_FEATURES.player_id.index

_PLAYER_SELF = 1
_PLAYER_HOSTILE = 4
_ARMY_SUPPLY = 5

_TERRAN_COMMANDCENTER = 18
_TERRAN_SCV = 45
_TERRAN_SUPPLY_DEPOT = 19
_TERRAN_BARRACKS = 21
_NEUTRAL_MINERAL_FIELD = 341

_NOT_QUEUED = [0]
_QUEUED = [1]
_SELECT_ALL = [2]

DATA_FILE = 'refined_agent_data_2'

ACTION_DO_NOTHING = 'donothing'
ACTION_BUILD_SUPPLY_DEPOT = 'buildsupplydepot'
ACTION_BUILD_BARRACKS = 'buildbarracks'
ACTION_BUILD_MARINE = 'buildmarine'
ACTION_ATTACK = 'attack'

smart_actions = [
    ACTION_DO_NOTHING,
    ACTION_BUILD_SUPPLY_DEPOT,
    ACTION_BUILD_BARRACKS,
    ACTION_BUILD_MARINE,
]

# SQLAlchemy Connection
engine = create_engine('mysql+pymysql://root:ABcd1234@localhost/sc2_stats')
Base = declarative_base()

# Create Database if it does not exist
if not database_exists(engine.url):
    create_database(engine.url)


# Create the Table if it does not exist
class Stats(Base):
    __tablename__ = 'stats_2'
    id = Column(Integer, primary_key=True)
    created = Column(DateTime, nullable=False)
    outcome = Column(Integer, nullable=False)
    game_score = Column(Integer, nullable=False)
    duration = Column(Float, nullable=False)


session = sessionmaker(bind=engine)
Base.metadata.create_all(engine)

for mm_x in range(0, 64):
    for mm_y in range(0, 64):
        if (mm_x + 1) % 32 == 0 and (mm_y + 1) % 32 == 0:
            smart_actions.append(ACTION_ATTACK + '_' + str(mm_x - 16) + '_' + str(mm_y - 16))


# Stolen from https://github.com/MorvanZhou/Reinforcement-learning-with-tensorflow
class QLearningTable:
    def __init__(self, actions, learning_rate=0.01, reward_decay=0.9, e_greedy=0.9):
        self.actions = actions  # a list
        self.lr = learning_rate
        self.gamma = reward_decay
        self.epsilon = e_greedy
        self.q_table = pd.DataFrame(columns=self.actions, dtype=np.float64)
        self.disallowed_actions = {}

    def choose_action(self, observation, excluded_actions=None):
        if excluded_actions is None:
            excluded_actions = []

        self.check_state_exist(observation)

        self.disallowed_actions[observation] = excluded_actions

        state_action = self.q_table.ix[observation, :]

        for excluded_action in excluded_actions:
            del state_action[excluded_action]

        if np.random.uniform() < self.epsilon:
            # some actions have the same value
            state_action = state_action.reindex(np.random.permutation(state_action.index))

            action = state_action.idxmax()
        else:
            action = np.random.choice(state_action.index)

        return action

    def learn(self, s, a, r, s_):
        if s == s_:
            return

        self.check_state_exist(s_)
        self.check_state_exist(s)

        q_predict = self.q_table.ix[s, a]

        s_rewards = self.q_table.ix[s_, :]

        if s_ in self.disallowed_actions:
            for excluded_action in self.disallowed_actions[s_]:
                del s_rewards[excluded_action]

        if s_ != 'terminal':
            q_target = r + self.gamma * s_rewards.max()
        else:
            q_target = r  # next state is terminal

        # update
        self.q_table.ix[s, a] += self.lr * (q_target - q_predict)

    def check_state_exist(self, state):
        if state not in self.q_table.index:
            # append new state to q table
            self.q_table = self.q_table.append(
                pd.Series([0] * len(self.actions), index=self.q_table.columns, name=state))


class SparseAgent(base_agent.BaseAgent):
    def __init__(self):
        super(SparseAgent, self).__init__()

        self.qlearn = QLearningTable(actions=list(range(len(smart_actions))))

        self.previous_action = None
        self.previous_state = None

        self.cc_y = None
        self.cc_x = None

        self.base_top_left = None

        self.move_number = 0

        """ Start the Time (for Statistics) """
        self.timer_start = time.process_time()

        if os.path.isfile(DATA_FILE + '.gz'):
            self.qlearn.q_table = pd.read_pickle(DATA_FILE + '.gz', compression='gzip')

    def transformDistance(self, x, x_distance, y, y_distance):
        if not self.base_top_left:
            return [x - x_distance, y - y_distance]

        return [x + x_distance, y + y_distance]

    def transformLocation(self, x, y):
        if not self.base_top_left:
            return [64 - x, 64 - y]

        return [x, y]

    def splitAction(self, action_id):
        smart_action = smart_actions[action_id]

        x = 0
        y = 0
        if '_' in smart_action:
            smart_action, x, y = smart_action.split('_')

        return (smart_action, x, y)

    def get_units_by_type(self, obs, unit_type):
        return [unit for unit in obs.observation.feature_units
                if unit.unit_type == unit_type]

    def unit_type_is_selected(self, obs, unit_type):
        if (len(obs.observation.single_select) > 0 and
                obs.observation.single_select[0].unit_type == unit_type):
            return True

        if (len(obs.observation.multi_select) > 0 and
                obs.observation.multi_select[0].unit_type == unit_type):
            return True

        return False

    def can_do(self, obs, action):
        return action in obs.observation.available_actions

    def step(self, obs):
        super(SparseAgent, self).step(obs)

        if obs.last():
            reward = obs.reward

            self.qlearn.learn(str(self.previous_state), self.previous_action, reward, 'terminal')

            self.qlearn.q_table.to_pickle(DATA_FILE + '.gz', 'gzip')

            self.previous_action = None
            self.previous_state = None

            self.move_number = 0

            """ Stop the Timer (Statistics) """
            time_diff = time.process_time() - self.timer_start

            """ Save the Statistic to the Database """
            s = session()
            game_score = int(obs.observation.score_cumulative[0])
            stats = Stats(created=datetime.datetime.utcnow(), outcome=obs.reward,
                          game_score=game_score, duration=time_diff)
            s.add(stats)
            s.commit()

            return actions.FunctionCall(_NO_OP, [])

        if obs.first():
            """ Get the position of the Player """
            player_y, player_x = (obs.observation.feature_minimap.player_relative ==
                                  features.PlayerRelative.SELF).nonzero()

            """ Set the Base top left """
            self.base_top_left = 1 if player_y.any() and player_y.mean() <= 31 else 0

            """ Assign the Command Center Coords for building the other buildings around it"""
            self.cc_x = player_x.mean()
            self.cc_y = player_y.mean()

        """ Check if we have a CommandCenter """
        command_centers = self.get_units_by_type(obs, units.Terran.CommandCenter)
        cc_count = len(command_centers)

        """ Count the Supply Depots """
        supply_depots = self.get_units_by_type(obs, units.Terran.SupplyDepot)
        supply_depot_count = len(supply_depots)

        """ Count the Barracks """
        barracks = self.get_units_by_type(obs, units.Terran.Barracks)
        barracks_count = len(barracks)

        """ Get the available supply """
        supply_free = (obs.observation.player.food_cap - obs.observation.player.food_used)

        """ Get the Army Supply """
        army_supply = obs.observation.player.food_army

        """ Get the Worker Supply """
        worker_supply = obs.observation.player.food_workers

        """ Multi Step Action """
        if self.move_number == 0:
            self.move_number += 1

            """ Init the Current States with Zeros (size = 12) """
            current_state = np.zeros(12)

            """ Assign the first States """
            current_state[0] = cc_count
            current_state[1] = supply_depot_count
            current_state[2] = barracks_count
            current_state[3] = army_supply

            """ Detect where the Enemy is and create 4 danger zones """
            hot_squares = np.zeros(4)
            enemy_y, enemy_x = (
                    obs.observation.feature_minimap.player_relative == features.PlayerRelative.ENEMY).nonzero()
            for i in range(0, len(enemy_y)):
                y = int(math.ceil((enemy_y[i] + 1) / 32))
                x = int(math.ceil((enemy_x[i] + 1) / 32))

                hot_squares[((y - 1) * 2) + (x - 1)] = 1

            """ If we are not in the Top Left Corner, invert """
            if not self.base_top_left:
                hot_squares = hot_squares[::-1]

            """ Assign Danger Zones to States """
            for i in range(0, 4):
                current_state[i + 4] = hot_squares[i]

            """ Detect where we are and create 4 friendly zones """
            green_squares = np.zeros(4)
            friendly_y, friendly_x = (
                    obs.observation.feature_minimap.player_relative == features.PlayerRelative.SELF).nonzero()
            for i in range(0, len(friendly_y)):
                y = int(math.ceil((friendly_y[i] + 1) / 32))
                x = int(math.ceil((friendly_x[i] + 1) / 32))

                green_squares[((y - 1) * 2) + (x - 1)] = 1

            """ If we are not in the Top Left Corner, invert """
            if not self.base_top_left:
                green_squares = green_squares[::-1]

            """ Assign Friendly Zones to States """
            for i in range(0, 4):
                current_state[i + 8] = green_squares[i]

            """ Send the States to the QTable """
            if self.previous_action is not None:
                self.qlearn.learn(str(self.previous_state), self.previous_action, 0, str(current_state))

            """ In order to speed up learning, we exclude some Actions """
            excluded_actions = []

            """ If we have no workers or if we already have 2 Supply Depots """
            if supply_depot_count == 2 or worker_supply == 0:
                excluded_actions.append(1)

            """ If we have No Supply Depots, Barracks or Workers """
            if supply_depot_count == 0 or barracks_count == 2 or worker_supply == 0:
                excluded_actions.append(2)

            """ If we have no Supply available or no Barracks build """
            if supply_free == 0 or barracks_count == 0:
                excluded_actions.append(3)

            """ If we have no Army """
            if army_supply == 0:
                excluded_actions.append(4)
                excluded_actions.append(5)
                excluded_actions.append(6)
                excluded_actions.append(7)

            """ Choose an Action from QTable """
            rl_action = self.qlearn.choose_action(str(current_state), excluded_actions)

            """ Reset the current to previous for next round (only in move_number = 0) """
            self.previous_state = current_state
            self.previous_action = rl_action

            """ Get the Smart Action """
            smart_action, x, y = self.splitAction(self.previous_action)

            if smart_action == ACTION_BUILD_BARRACKS or smart_action == ACTION_BUILD_SUPPLY_DEPOT:
                """ BUILD_BARRACKS or BUILD_SUPPLY_DEPOT - MOVE 1: Select SCV """
                scvs = self.get_units_by_type(obs, units.Terran.SCV)

                if len(scvs) > 0:
                    scv = random.choice(scvs)
                    return actions.FUNCTIONS.select_unit(scv)

            elif smart_action == ACTION_BUILD_MARINE:
                """ BUILD MARINE - MOVE 1: Select Barracks """
                if barracks_count > 0:
                    return actions.FUNCTIONS.select_point("select_all_type", barracks)
                    return actions.FunctionCall(_SELECT_POINT, [_SELECT_ALL, target])

            elif smart_action == ACTION_ATTACK:
                """ ACTION ATTACK - MOVE 1: Select Army """
                if _SELECT_ARMY in obs.observation['available_actions']:
                    return actions.FunctionCall(_SELECT_ARMY, [_NOT_QUEUED])

        elif self.move_number == 1:
            self.move_number += 1

            """ Get the Smart Action """
            smart_action, x, y = self.splitAction(self.previous_action)

            if smart_action == ACTION_BUILD_SUPPLY_DEPOT:
                """ BUILD_SUPPLY_DEPOT - MOVE 2: Move SCV to Target Location """
                target = None
                if supply_depot_count < 2 and _BUILD_SUPPLY_DEPOT in obs.observation['available_actions']:
                    if self.cc_y.any():
                        if supply_depot_count == 0:
                            target = self.transformDistance(round(self.cc_x.mean()), -35, round(self.cc_y.mean()), 0)
                        elif supply_depot_count == 1:
                            target = self.transformDistance(round(self.cc_x.mean()), -25, round(self.cc_y.mean()), -25)

                        return actions.FunctionCall(_BUILD_SUPPLY_DEPOT, [_NOT_QUEUED, target])

            elif smart_action == ACTION_BUILD_BARRACKS:
                """ BUILD_BARRACKS - MOVE 2: Move SCV to Target Location """
                target = None
                if barracks_count < 2 and _BUILD_BARRACKS in obs.observation['available_actions']:
                    if self.cc_y.any():
                        if barracks_count == 0:
                            target = self.transformDistance(round(self.cc_x.mean()), 15, round(self.cc_y.mean()), -9)
                        elif barracks_count == 1:
                            target = self.transformDistance(round(self.cc_x.mean()), 15, round(self.cc_y.mean()), 12)

                        return actions.FunctionCall(_BUILD_BARRACKS, [_NOT_QUEUED, target])

            elif smart_action == ACTION_BUILD_MARINE:
                """ BUILD_MARINE - MOVE 2: Train Marine """
                if _TRAIN_MARINE in obs.observation['available_actions']:
                    return actions.FunctionCall(_TRAIN_MARINE, [_QUEUED])

            elif smart_action == ACTION_ATTACK:
                """ ATTACK - MOVE 2: Move Army to Target """
                do_it = True

                """ Dont't do it if we have single selected an SCV """
                if len(obs.observation['single_select']) > 0 and obs.observation['single_select'][0][0] == _TERRAN_SCV:
                    do_it = False

                """ Dont't do it if we have multi selected SCVs """
                if len(obs.observation['multi_select']) > 0 and obs.observation['multi_select'][0][0] == _TERRAN_SCV:
                    do_it = False

                """ Move to Random Location with Attackmove """
                if do_it and _ATTACK_MINIMAP in obs.observation["available_actions"]:
                    x_offset = random.randint(-1, 1)
                    y_offset = random.randint(-1, 1)

                    return actions.FunctionCall(_ATTACK_MINIMAP, [_NOT_QUEUED,
                                                                  self.transformLocation(int(x) + (x_offset * 8),
                                                                                         int(y) + (y_offset * 8))])

        elif self.move_number == 2:
            self.move_number = 0

            """ Get the Smart Action """
            smart_action, x, y = self.splitAction(self.previous_action)

            if smart_action == ACTION_BUILD_BARRACKS or smart_action == ACTION_BUILD_SUPPLY_DEPOT:
                """ BUILD_BARRACKS or BUILD_SUPPLY_DEPOT - MOVE 3: Let the SCV return to Mineral Field """
                if _HARVEST_GATHER in obs.observation['available_actions']:
                    unit_y, unit_x = self.get_units_by_type(obs, units.Neutral.MineralField)

                    if unit_y.any():
                        i = random.randint(0, len(unit_y) - 1)

                        m_x = unit_x[i]
                        m_y = unit_y[i]

                        target = [int(m_x), int(m_y)]

                        return actions.FunctionCall(_HARVEST_GATHER, [_QUEUED, target])

        return actions.FUNCTIONS.no_op()
