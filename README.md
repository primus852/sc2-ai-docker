## SC2-AI Base Docker Image

### Setup
- clone Repo `git clone https://github.com/primus852/sc2-ai-docker`
- go to folder `cd sc2-ai-docker`
- Download latest StarCraft 2 linux Client: [SC2 Linux](https://github.com/Blizzard/s2client-proto#linux-packages)
- Move contents to `/sc2ai` (so the path is `/sc2ai/StarCraftII`)
- Download desired Maps: [SC2 Maps](https://github.com/Blizzard/s2client-proto#map-packs) and move the contents to `sc2ai/StarCraftII/Maps`
- Fire up the container `docker-compose up -d && docker-compose run sc2 bash`

__Attention__ With MinGW64 on Windows, use `docker-compose up -d && winpty docker-compose run sc2 bash` (prefixed with `winpty` for an interactive shell)

### Agent Usage (within the container)
- go to folder of custom client
- Start the Agent ` python3 -m pysc2.bin.agent --map Simple64 --agent agent.agent.DeepAgent --agent_race terran --norender`

__Attention__: DO NOT USE THE `parallel` flag, as there is an issue saving the qtable.

### Dashboard
- Navigate to `http://localhost:4620` to see your stats

### ToDO
- in order to make `parallel` flag available, the QTable loading/saving needs to be reworked
