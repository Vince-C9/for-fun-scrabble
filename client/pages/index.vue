<script setup>
import { usePlayerStore } from "@/stores/playerStore";
import { ref, onMounted, shallowRef } from "vue";
import { useRouter } from "vue-router";

const { $api } = useNuxtApp();
const gameList = ref([]);
const loading = shallowRef(false);
const playerStore = usePlayerStore();
const router = useRouter();
const playerName = ref("");
const joining = shallowRef(false);

// Modal state
const showModal = ref(false);
const selectedGameId = ref(null);

const getGames = () => {
    $api.get("/game")
        .then((response) => {
            gameList.value = response.data.data;
        })
        .finally(() => {
            loading.value = false;
        });
};

onMounted(() => {
    loading.value = true;
    getGames();
    playerStore.getGamesFromLocalStorage(); // Load stored games
});

// Handle game join button
const handleJoinGame = (game) => {
    if (playerStore.isInGame(game.id)) {
        router.push(`/game/play/${game.id}`);
    } else {
        selectedGameId.value = game.id;
        showModal.value = true;
    }
};

// Confirm joining the game
const confirmJoinGame = () => {
    if (selectedGameId.value !== null) {
        $api.post(`/game/${selectedGameId.value}/join`, {
            player: playerName.value,
        })
            .catch((err) => {
                console.log(err);
            })
            .then((response) => {
                playerStore.joinGame(
                    response.data.data.game_id,
                    response.data.data.id
                );
                router.push(`/game/play/${response.data.data.id}`);
            })
            .finally(() => {
                showModal.value = false;
            });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-GB"); // en-GB gives DD/MM/YYYY
};
</script>

<template>
    <div class="max-w-5xl mx-auto p-6">
        <!-- Loader -->
        <div
            v-if="loading"
            class="flex justify-center items-center min-h-[300px]"
        >
            <Loader />
        </div>

        <!-- Game List -->
        <div v-else>
            <div class="flex justify-between items-center mb-6">
                <h2
                    class="text-2xl font-semibold text-gray-900 dark:text-white"
                >
                    Active Games
                </h2>
                <NuxtLink to="/game/create">
                    <button
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow"
                    >
                        Create New Game
                    </button>
                </NuxtLink>
            </div>

            <!-- No Games Found -->
            <p
                v-if="gameList.length === 0"
                class="text-gray-500 dark:text-gray-400 text-center py-10"
            >
                No active games found...
            </p>

            <!-- Game Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    v-for="game in gameList"
                    :key="game.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-300 dark:border-gray-700"
                >
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        Game #{{ game.id }}
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 mt-1">
                        Created By: {{ game.players[0]?.name ?? "Unknown" }}
                    </p>
                    <div
                        class="flex justify-between items-center text-gray-600 dark:text-gray-400 mt-4 text-sm"
                    >
                        <span
                            >Status: <strong>{{ game.state }}</strong></span
                        >
                        <span>Created: {{ formatDate(game.created_at) }}</span>
                    </div>
                    <button
                        class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition"
                        @click="handleJoinGame(game)"
                    >
                        Join Game
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Join Confirmation Modal -->
    <div
        v-if="showModal"
        class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50"
    >
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                Join Game
            </h2>

            <p class="text-gray-700 dark:text-gray-300 mt-2">
                Are you sure you want to join Game #{{ selectedGameId }}?
            </p>

            <!-- Player Name Input Field -->
            <div class="mt-4">
                <label
                    for="playerName"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Enter Your Player Name:
                </label>
                <input
                    id="playerName"
                    v-model="playerName"
                    type="text"
                    placeholder="Your name"
                    class="mt-1 w-full p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <!-- Modal Buttons -->
            <div class="flex justify-end mt-4 space-x-3" v-show="!joining">
                <button
                    @click="showModal = false"
                    class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg"
                >
                    Cancel
                </button>
                <button
                    @click="confirmJoinGame"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
                >
                    Join
                </button>
            </div>
            <Loader v-show="joining" />
        </div>
    </div>
</template>
