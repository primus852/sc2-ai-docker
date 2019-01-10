import sc2
from sc2 import run_game, maps, Race, Difficulty
from sc2.player import Bot, Computer
from sc2.constants import NEXUS, PROBE


class NeuralBot(sc2.BotAI):

    async def on_step(self, iteration: int):
        """ Wait for the workers to mine """
        await self.distribute_workers()
        await self.build_workers()

    async def build_workers(self):
        for nexus in self.units(NEXUS).ready.noqueue:
            if self.can_afford(PROBE):
                await self.do(nexus.train(PROBE))


run_game(maps.get("AbyssalReefLE"), [
    Bot(Race.Protoss, NeuralBot()),
    Computer(Race.Terran, Difficulty.Easy)
], realtime=False)
