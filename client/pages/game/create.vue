<script setup>
import { usePlayerStore } from "~/stores/playerStore";

const { $api } = useNuxtApp();
const playerStore = usePlayerStore();

const playerName = ref("");
const successMessage = ref("");
const errorMessage = ref("");
const loading = shallowRef(false);

async function createGame() {
    successMessage.value = "";
    errorMessage.value = "";
    loading.value = true;

    await $api
        .post("/game/create", {
            player: playerName.value,
            state: "seeking_players",
        })
        .catch((error) => {
            loading.value = false;
            console.log(error);
            errorMessage.value = error.response.data.message;
        })
        .then((response) => {
            loading.value = false;
            console.log("RESP", response.data.data.id);
            successMessage.value = "Game created successfully!";

            playerStore.joinGame(
                response.data.data.id,
                response.data.data.players[0].id
            );
            console.log(playerStore.state);
            navigateTo(`/game/play/${response.data.data.id}`);
        });
}
</script>
<template>
    <div class="flex justify-between">
        <h2 class="text-xl font-semibold text-black dark:text-white">
            Create a Game
        </h2>
    </div>
    <div>
        <div
            class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
            role="alert"
            v-if="successMessage"
        >
            {{ successMessage }}
        </div>
        <div
            class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300"
            role="alert"
            v-if="errorMessage"
        >
            {{ errorMessage }}
        </div>
        <form class="max-w-sm mx-auto">
            <div class="mb-5">
                <label
                    for="input"
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >Host Player Name</label
                >
                <input
                    type="input"
                    id="input"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    v-model="playerName"
                    required
                />
            </div>
            <button
                :disabled="loading"
                type="button"
                @click="createGame"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
            >
                <span v-show="loading"> Loading... </span>
                <span v-show="!loading">Submit</span>
            </button>
        </form>
    </div>
</template>
