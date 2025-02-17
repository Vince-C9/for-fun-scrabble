<script setup>
const playerName = ref("");
const gameId = useRoute().params.id;
</script>
<template>
    <div class="flex justify-between">
        <h2 class="text-xl font-semibold text-black dark:text-white">
            Playing Game {{ gameId }}
        </h2>
    </div>
    <div>
        <ScrabbleBoard />
    </div>
</template>
