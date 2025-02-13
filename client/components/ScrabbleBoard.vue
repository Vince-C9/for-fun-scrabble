<script setup>
import { ref, computed, watch } from "vue";

const playerStore = usePlayerStore();
const api = useNuxtApp().$api;
const route = useRoute();
const loading = shallowRef(false);
const playerIsInGame = shallowRef(false);

// Tile Pool (only available for active player)
const tilePool = ref([]);

// Computed: Check if it's the logged-in player's turn
const isMyTurn = computed(
    () => players.value[activePlayerIndex.value].id === loggedInPlayerId.value
);

const gameDetails = ref({});

onMounted(() => {
    getGameDetails();
    playerStore.getGamesFromLocalStorage();
});

watch(
    () => gameDetails.value,
    () => {
        players.value = playerArrayToObject(gameDetails.value.players);
        playerIsInGame.value = playerStore.isInGame(gameDetails.value.id);
        mapPlayerStoreToGame();
        getPlayerTilesFromServer();
    }
);

const getPlayerTilesFromServer = () => {
    api.get(`/game/turn/${route.params.id}/${loggedInPlayerId.value}`)
        .then((response) => {
            tilePool.value = response.data.tiles.map((tile) => tile.tile);
        })
        .catch((error) => {
            console.log(error);
        });
};
const mapPlayerStoreToGame = () => {
    const game = playerStore.games.find(function (game) {
        return game.gameId === +route.params.id;
    });
    if (game) {
        loggedInPlayerId.value = game.playerId;
    }
};

const playerArrayToObject = (playerArray) => {
    return playerArray.map((player) => {
        return {
            id: player.id,
            name: player.name,
            score: player.score,
        };
    });
};

const getGameDetails = async () => {
    loading.value = true;
    await api
        .get(`/game/${route.params.id}`)
        .catch((error) => {
            console.log(error);
        })
        .then((response) => {
            gameDetails.value = response.data.data;
            players.value = playerArrayToObject(gameDetails.value.players);

            // Load placed tiles
            if (gameDetails.value.tiles) {
                gameDetails.value.tiles.forEach((tile) => {
                    if (!!tile.x && !!tile.y) {
                        board.value[tile.y][tile.x].tile = {
                            letter: tile.letter,
                            score: tile.score,
                        };
                        board.value[tile.y][tile.x].locked = true;
                    }
                });
            }
        })
        .finally(() => {
            loading.value = false;
        });
};
//methods

const boardSize = 15;

// Standard Scrabble board bonuses
const bonuses = [
    ["TW", "", "", "DL", "", "", "", "TW", "", "", "", "DL", "", "", "TW"],
    ["", "DW", "", "", "", "TL", "", "", "", "TL", "", "", "", "DW", ""],
    ["", "", "DW", "", "", "", "DL", "", "DL", "", "", "", "DW", "", ""],
    ["DL", "", "", "DW", "", "", "", "DL", "", "", "", "DW", "", "", "DL"],
    ["", "", "", "", "DW", "", "", "", "", "", "DW", "", "", "", ""],
    ["", "TL", "", "", "", "TL", "", "", "", "TL", "", "", "", "TL", ""],
    ["", "", "DL", "", "", "", "DL", "", "DL", "", "", "", "DL", "", ""],
    ["TW", "", "", "DL", "", "", "", "DW", "", "", "", "DL", "", "", "TW"],
    ["", "", "DL", "", "", "", "DL", "", "DL", "", "", "", "DL", "", ""],
    ["", "TL", "", "", "", "TL", "", "", "", "TL", "", "", "", "TL", ""],
    ["", "", "", "", "DW", "", "", "", "", "", "DW", "", "", "", ""],
    ["DL", "", "", "DW", "", "", "", "DL", "", "", "", "DW", "", "", "DL"],
    ["", "", "DW", "", "", "", "DL", "", "DL", "", "", "", "DW", "", ""],
    ["", "DW", "", "", "", "TL", "", "", "", "TL", "", "", "", "DW", ""],
    ["TW", "", "", "DL", "", "", "", "TW", "", "", "", "DL", "", "", "TW"],
];

// Initialize board
const board = ref(
    Array.from({ length: boardSize }, (_, rowIndex) =>
        Array.from({ length: boardSize }, (_, colIndex) => ({
            tile: null,
            bonus: bonuses[rowIndex][colIndex] || "",
            locked: false,
        }))
    )
);

// Players
const players = ref([
    { id: 1, name: "Alice", score: 0, tiles: [] },
    { id: 2, name: "Bob", score: 0, tiles: [] },
    { id: 3, name: "Charlie", score: 0, tiles: [] },
]);

const loggedInPlayerId = ref(1); // Assume the logged-in player is Bob
const activePlayerIndex = ref(0);

// Score Calculation
// Add existing letters into score
/*const currentWordScore = computed(() => {
    let score = 0;
    let wordMultiplier = 1;

    board.value.forEach((row) => {
        row.forEach((cell) => {
            if (cell.tile && !cell.locked) {
                let letterScore = cell.tile.score;

                if (cell.bonus === "DL") letterScore *= 2;
                if (cell.bonus === "TL") letterScore *= 3;
                if (cell.bonus === "DW") wordMultiplier *= 2;
                if (cell.bonus === "TW") wordMultiplier *= 3;

                score += letterScore;
            }
        });
    });

    return score * wordMultiplier;
});*/

const currentWordScore = computed(() => {
    let totalScore = 0;
    let checkedCells = new Set(); // To track tiles already scored

    // Find the full word in a given direction
    const findFullWord = (row, col, direction) => {
        let wordScore = 0;
        let wordMultiplier = 1;
        let word = "";
        //we take a copy of the row and column index to avoid modifying original board state/data.
        let r = row,
            c = col;

        // Move backward to find the start of the word
        while (r > 0 && direction === "vertical" && board.value[r - 1][c]?.tile)
            r--;
        while (
            c > 0 &&
            direction === "horizontal" &&
            board.value[r][c - 1]?.tile
        )
            c--;

        // Move forward to collect the word and calculate score
        //This will include any existing tiles placed on the board.
        while (
            r < board.value.length &&
            c < board.value[r].length &&
            board.value[r][c]?.tile
        ) {
            const cell = board.value[r][c];

            if (!checkedCells.has(`${r},${c}`)) {
                let letterScore = cell.tile.score;

                // Apply letter bonuses
                if (cell.bonus === "DL") letterScore *= 2;
                if (cell.bonus === "TL") letterScore *= 3;

                // Apply word bonuses
                if (cell.bonus === "DW") wordMultiplier *= 2;
                if (cell.bonus === "TW") wordMultiplier *= 3;

                wordScore += letterScore;
                checkedCells.add(`${r},${c}`); // Mark as scored
            }

            word += cell.tile.letter;
            if (direction === "horizontal") c++;
            else r++;
        }

        return {
            word,
            score: word.length > 1 ? wordScore * wordMultiplier : 0,
        };
    };

    // Scan for new tiles placed this turn
    board.value.forEach((row, rowIndex) => {
        row.forEach((cell, colIndex) => {
            if (
                cell.tile &&
                !cell.locked &&
                !checkedCells.has(`${rowIndex},${colIndex}`)
            ) {
                // **Determine primary direction of the word**
                let hasHorizontal =
                    (colIndex > 0 &&
                        board.value[rowIndex][colIndex - 1]?.tile) ||
                    (colIndex < board.value[rowIndex].length - 1 &&
                        board.value[rowIndex][colIndex + 1]?.tile);

                let hasVertical =
                    (rowIndex > 0 &&
                        board.value[rowIndex - 1][colIndex]?.tile) ||
                    (rowIndex < board.value.length - 1 &&
                        board.value[rowIndex + 1][colIndex]?.tile);

                let primaryDirection =
                    hasHorizontal && !hasVertical
                        ? "horizontal"
                        : hasVertical && !hasHorizontal
                        ? "vertical"
                        : null;

                if (!primaryDirection) primaryDirection = "horizontal"; // Default

                // **Score the main word**
                const { word: mainWord, score: mainScore } = findFullWord(
                    rowIndex,
                    colIndex,
                    primaryDirection
                );
                totalScore += mainScore;

                // **Score perpendicular words** (formed by newly placed tile)
                let perpendicularDirection =
                    primaryDirection === "horizontal"
                        ? "vertical"
                        : "horizontal";
                const { word: perpendicularWord, score: perpendicularScore } =
                    findFullWord(rowIndex, colIndex, perpendicularDirection);
                totalScore += perpendicularScore;
            }
        });
    });

    return totalScore;
});

// Drag & Drop Logic
const startDrag = (event, tile, index) => {
    event.dataTransfer.setData("tile", JSON.stringify({ tile, index }));
};

//Dynamically create words including existing tiles, update scores
const placeTile = (event, row, col) => {
    if (!isMyTurn.value) return;
    const data = JSON.parse(event.dataTransfer.getData("tile"));
    if (data && !board.value[row][col].tile && !board.value[row][col].locked) {
        board.value[row][col].tile = data.tile;
        tilePool.value.splice(data.index, 1);
    }
};

const removeTile = (row, col) => {
    if (!isMyTurn.value || board.value[row][col].locked) return;
    tilePool.value.push(board.value[row][col].tile);
    board.value[row][col].tile = null;
};

// Submit Word & Rotate Turns
//Refactor to work out (use score tracker for inspiration)
// 1. Current Word Score
// 2. Tiles used & any bonuses applied.
// 3. Include existing / already placed tiles.
const submitWord = () => {
    players.value[activePlayerIndex.value].score += currentWordScore.value;

    board.value.forEach((row) =>
        row.forEach((cell) => {
            if (cell.tile) cell.locked = true;
        })
    );

    activePlayerIndex.value =
        (activePlayerIndex.value + 1) % players.value.length;
};

// Bonus Class Logic
const getBonusClass = (row, col) =>
    bonuses[row][col]
        ? `bg-${bonuses[row][col] === "TW" ? "red-500" : "blue-300"} `
        : "bg-green-300 text-gray-700";
</script>

<template>
    <div class="flex justify-center" v-if="loading">
        <Loader />
    </div>
    <div v-else class="flex flex-row min-h-screen bg-gray-100 p-6">
        <pre>{{ players.value }}</pre>
        <!-- Scrabble Board -->
        <div class="flex flex-col items-center flex-grow">
            <h1 class="text-2xl font-bold mb-4">Scrabble Board</h1>

            <!-- Active Player Score Display -->
            <div
                v-if="isMyTurn"
                class="mb-4 px-6 py-2 bg-yellow-400 text-black font-bold text-lg rounded shadow"
            >
                Current Score: {{ currentWordScore ?? 0 }}
            </div>
            <div
                v-else
                class="mb-4 px-6 py-2 bg-gray-400 text-black font-bold text-lg rounded shadow"
            >
                It is {{ players[activePlayerIndex].name }}'s turn
            </div>

            <!-- Tile Pool (Only for Active Player) -->
            <div v-if="isMyTurn" class="flex flex-col items-center mb-6">
                <h2 class="text-lg font-bold mb-3">Tile Pool</h2>
                <div
                    class="grid grid-cols-7 gap-2 p-4 bg-white shadow rounded-lg"
                >
                    <div
                        v-for="(tile, index) in tilePool"
                        :key="index"
                        class="relative w-12 h-12 bg-yellow-300 text-lg font-bold flex items-center justify-center rounded shadow cursor-grab active:scale-95"
                        draggable="true"
                        @dragstart="startDrag($event, tile, index)"
                    >
                        {{ tile.letter }}
                        <span
                            class="absolute bottom-1 right-1 text-xs text-gray-800"
                            >{{ tile.score }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Scrabble Board -->
            <div
                class="grid grid-cols-15 gap-1 bg-green-600 p-2 shadow-lg rounded-lg"
            >
                <div
                    v-for="(row, rowIndex) in board"
                    :key="rowIndex"
                    class="contents"
                >
                    <div
                        v-for="(cell, colIndex) in row"
                        :key="colIndex"
                        class="w-12 h-12 border border-green-700 flex items-center justify-center text-xl font-bold rounded-md relative"
                        :class="[
                            cell.tile
                                ? 'bg-yellow-400 text-black shadow-lg cursor-pointer'
                                : getBonusClass(rowIndex, colIndex),
                            !isMyTurn ? 'opacity-50 cursor-not-allowed' : '', // Restrict interaction
                        ]"
                        @dragover.prevent
                        @drop="
                            isMyTurn
                                ? placeTile($event, rowIndex, colIndex)
                                : null
                        "
                        @click="
                            isMyTurn ? removeTile(rowIndex, colIndex) : null
                        "
                    >
                        {{ cell.tile?.letter || cell.bonus }}
                        <!-- Score Counter -->
                        <span
                            v-if="cell.tile"
                            class="absolute bottom-1 right-1 text-xs text-gray-800"
                        >
                            {{ cell.tile.score }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button
                v-if="isMyTurn"
                class="mt-6 px-6 py-2 bg-blue-500 text-white font-bold rounded shadow hover:bg-blue-700 transition"
                @click="submitWord"
            >
                Submit Word
            </button>
        </div>

        <!-- Player List (Right Sidebar) -->
        <div class="w-60 bg-white shadow-lg rounded-lg p-4 ml-6">
            <h2 class="text-lg font-bold mb-4">Players</h2>
            <ul>
                <li
                    v-for="(player, index) in players"
                    :key="index"
                    class="p-2 rounded-md text-lg font-semibold flex items-center"
                    :class="
                        activePlayerIndex === index
                            ? 'bg-green-400 text-white'
                            : 'bg-gray-200 text-gray-800'
                    "
                >
                    {{ player.name }} ({{ player.score }} pts)
                    <span
                        v-if="player.id === loggedInPlayerId"
                        class="ml-auto text-sm text-gray-600"
                        >(You)</span
                    >
                </li>
            </ul>
        </div>
    </div>
</template>
