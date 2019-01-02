## SC2-AI Base Docker Image

### Setup
- clone Repo `git clone https://github.com/primus852/sc2-ai-docker`
- go to folder `cd sc2-ai-docker`
- Download latest StarCraft 2 linux Client: [SC2 Linux](https://github.com/Blizzard/s2client-proto#linux-packages)
- Move contents to `/sc2ai` (so the path is `/sc2ai/StarCraftII`)
- Download desired Maps: [SC2 Maps](https://github.com/Blizzard/s2client-proto#map-packs) and mobe the contents to `sc2ai/StarCraftII/Maps`
- Fire up the container `docker-compose up -d && docker-compose run sc2 bash`

__Attention__ With MinGW64 on Windows, use `docker-compose up -d && winpty docker-compose run sc2 bash` (prefixed with `winpty` for an interactive shell)

### Usage (within the container)
- Create a Custom Agent (or clone [https://github.com/primus852/SC2-AI-Reinforced](https://github.com/primus852/SC2-AI-Reinforced) to begin with)
- go to folder of custom client
- Start the Agent `python3 -m pysc2.bin.agent --map Simple64 --agent refined.DeepAgent --agent_race terran --norender --parallel 2`

### Dashboard
#### Install Symfony Project
- go to `dashboard` folder `cd /sc2ai/dashboard`
- Install dependencies: `composer install`
- Edit `.env` with the DB Settings: `sqlite:////%kernel.project_dir%/../../sc2ai/SC2-AI-Reinforced/db/stats.db` (or any other db)
#### Install NPM Dependencies
- go to `public` folder `cd /sc2ai/dashboard/public`
- Install dependencies: `npm install`
#### View in Browser
- Navigate to http://localhost:4620 to see your stats
