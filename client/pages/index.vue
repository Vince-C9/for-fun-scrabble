<script setup>
const { $api } = useNuxtApp();
const gameList = ref([]);
const loading = shallowRef(false);
const getGames = () => {
    $api.get("/game")
        .then((response) => {
            gameList.value = response.data.data;
            console.log(response.data);
        })
        .finally(() => {
            loading.value = false;
        });
};

onMounted(() => {
    loading.value = true;
    getGames();
});
</script>
<template>
    <div class="flex justify-center" v-if="loading">
        <Loader />
    </div>
    <div v-else>
        <div class="flex justify-between">
            <h2 class="text-xl font-semibold text-black dark:text-white">
                Active Games
            </h2>
            <NuxtLink to="/game/create">
                <button
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer"
                    @click="showNewGame = true"
                >
                    Make a Game
                </button>
            </NuxtLink>
        </div>
        <div class="grid grid-cols-1 gap-4 mt-4">
            <p v-if="gameList.length == 0">No current active games...</p>

            <div
                class="p-4 width-full bg-gray-200 dark:bg-gray-700"
                v-for="game in gameList"
                v-if="gameList.length > 0"
            >
                <h1 class="text-2xl font-semibold leading-6 text-gray-900">
                    Game {{ game.id }}
                </h1>
                <p class="mt-2">
                    Created By: {{ game.players[0]?.name ?? "Unknown Player" }}
                </p>
                <div class="flex justify-between mb-4 mt-4">
                    <span>Status: {{ game.state }}</span>
                    <span>Created: {{ game.created_at }}</span>
                </div>
                <NuxtLink :to="'/game/play/' + game.id">
                    <button
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer"
                    >
                        Join
                    </button>
                </NuxtLink>
            </div>
        </div>
    </div>
</template>
