import sc2
from sc2 import run_game, maps, Race, Difficulty
from sc2.player import Bot, Computer


class NeuralBot(sc2.BotAI):
    async def on_step(self, iteration: int):
        """ Wait for the workers to mine """
        await self.distribute_workers()


run_game(maps.get("/root/StarCraftII/Maps/AbyssalReefLE.SC2Map"), [
    Bot(Race.Protoss, NeuralBot()),
    Computer(Race.Terran, Difficulty.Easy)
], realtime=True)
