<script setup>
import { ref, computed, watch } from "vue";

const playerStore = usePlayerStore();
const api = useNuxtApp().$api;
const route = useRoute();
const loading = shallowRef(false);
const playerIsInGame = shallowRef(false);
const currentTurnWords = ref([]);
const submittingTurn = ref(false);
const nextPlayersTurn = ref(null);

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

    api.put(`/game/${route.params.id}/player/7/turn`);
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

            // Load placed tiles with their IDs
            if (gameDetails.value.tiles) {
                gameDetails.value.tiles.forEach((tile) => {
                    if (tile.x !== null && tile.y !== null) {
                        board.value[tile.y][tile.x].tile = {
                            id: tile.id, // Ensure tileId is stored
                            letter: tile.letter,
                            score: tile.score,
                        };
                        board.value[tile.y][tile.x].locked = true;
                    }
                });
            }

            activePlayerIndex.value = activePlayerIdToIndex(
                gameDetails.value.player_turn_id
            );
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
    let detectedWords = []; // Stores words detected this turn

    // Find the full word in a given direction
    const findFullWord = (rowIndex, columnIndex, direction) => {
        let wordScore = 0;
        let wordMultiplier = 1;
        let wordString = "";
        let tilesUsed = [];

        // Copy row and column index to prevent modifying board state
        let r = rowIndex,
            c = columnIndex;

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
        while (
            r < board.value.length &&
            c < board.value[r].length &&
            board.value[r][c]?.tile
        ) {
            const cell = board.value[r][c];

            let letterScore = cell.tile.score;
            let letterBonuses = [];

            // Apply letter bonuses
            if (cell.bonus === "DL") {
                letterScore *= 2;
                letterBonuses.push("DL");
            }
            if (cell.bonus === "TL") {
                letterScore *= 3;
                letterBonuses.push("TL");
            }

            // Apply word bonuses
            if (cell.bonus === "DW") {
                wordMultiplier *= 2;
                letterBonuses.push("DW");
            }
            if (cell.bonus === "TW") {
                wordMultiplier *= 3;
                letterBonuses.push("TW");
            }

            console.log("x", c, "y", r);
            // Track tiles used
            tilesUsed.push({
                tileId: cell.tile.id,
                letter: cell.tile.letter,
                letterScore: cell.tile.score,
                letterBonuses: letterBonuses,
                totalTileScore: letterScore,
                x: c,
                y: r,
            });

            wordScore += letterScore;
            wordString += cell.tile.letter;
            checkedCells.add(`${r},${c}`); // Mark as scored

            if (direction === "horizontal") c++;
            else r++;
        }

        // Only return words of length > 1
        if (wordString.length > 1) {
            return {
                wordString,
                wordScore: wordScore * wordMultiplier,
                wordBonuses: wordMultiplier > 1 ? ["DW", "TW"] : [],
                tiles: tilesUsed,
            };
        }
        return null;
    };

    // Scan for new tiles placed this turn
    board.value.forEach((row, rowIndex) => {
        row.forEach((cell, columnIndex) => {
            if (
                cell.tile &&
                !cell.locked &&
                !checkedCells.has(`${rowIndex},${columnIndex}`)
            ) {
                // **Determine primary direction of the word**
                let hasHorizontal =
                    (columnIndex > 0 &&
                        board.value[rowIndex][columnIndex - 1]?.tile) ||
                    (columnIndex < board.value[rowIndex].length - 1 &&
                        board.value[rowIndex][columnIndex + 1]?.tile);

                let hasVertical =
                    (rowIndex > 0 &&
                        board.value[rowIndex - 1][columnIndex]?.tile) ||
                    (rowIndex < board.value.length - 1 &&
                        board.value[rowIndex + 1][columnIndex]?.tile);

                let primaryDirection =
                    hasHorizontal && !hasVertical
                        ? "horizontal"
                        : hasVertical && !hasHorizontal
                        ? "vertical"
                        : null;

                if (!primaryDirection) primaryDirection = "horizontal"; // Default

                // **Score the main word**
                const mainWord = findFullWord(
                    rowIndex,
                    columnIndex,
                    primaryDirection
                );
                if (mainWord) {
                    totalScore += mainWord.wordScore;
                    detectedWords.push(mainWord);
                }

                // **Score perpendicular words** (formed by newly placed tile)
                let perpendicularDirection =
                    primaryDirection === "horizontal"
                        ? "vertical"
                        : "horizontal";
                const perpendicularWord = findFullWord(
                    rowIndex,
                    columnIndex,
                    perpendicularDirection
                );
                if (perpendicularWord) {
                    totalScore += perpendicularWord.wordScore;
                    detectedWords.push(perpendicularWord);
                }
            }
        });
    });

    // Persist words found in the current turn
    currentTurnWords.value = detectedWords;

    return totalScore;
});

// Drag & Drop Logic
const startDrag = (event, tile, index) => {
    event.dataTransfer.setData("tile", JSON.stringify({ tile, index }));
};

//Dynamically create words including existing tiles, update scores
const placeTile = (event, row, col) => {
    if (!isMyTurn.value && !submittingTurn) return;
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
const submitWord = async () => {
    submittingTurn.value = true;
    const activePlayer = players.value[activePlayerIndex.value];

    // Calculate total score for this turn
    let totalScore = 0;
    let wordsArray = [];

    currentTurnWords.value.forEach((word) => {
        totalScore += word.wordScore;
        wordsArray.push({
            word: word.wordString,
            wordScore: word.wordScore,

            wordBonuses: word.wordBonuses ?? [], // Double/triple word bonuses
            tiles: word.tiles.map((tile) => ({
                id: tile.tileId, // Added Tile ID
                letter: tile.letter,
                baseScore: tile.letterScore,
                letterBonuses: tile.letterBonuses ?? [], // Double/triple letter bonuses
                totalTileScore: tile.totalTileScore,
                x: tile.x,
                y: tile.y,
            })),
        });
    });

    // Prepare data for backend submission
    const moveData = {
        totalScore: totalScore,
        words: wordsArray,
    };

    // Submit move to backend
    try {
        await api
            .post(
                `/game/${route.params.id}/player/${loggedInPlayerId.value}/submit`,
                moveData
            )
            .catch((error) => {
                console.error("Error submitting move:", error);
                alert(error.response.data.message);
            })
            .then((response) => {
                if (response.data.status === "success") {
                    // Increment the active player's score
                    activePlayer.score += totalScore;

                    board.value.forEach((row) => {
                        row.forEach((cell) => {
                            if (cell.tile) cell.locked = true;
                        });
                    });
                    getPlayerTilesFromServer();
                    // Cycle to the next player
                    activePlayerIndex.value =
                        (activePlayerIndex.value + 1) % players.value.length;

                    console.log("ACTIVE PLAYER INDEX SOFT", activePlayerIndex);
                    nextPlayersTurn.value = activePlayerIndexToPlayerId(
                        activePlayerIndex.value
                    );
                    console.log(
                        "The Next Player ID is: ",
                        nextPlayersTurn.value
                    );
                    //Update player turn on back end
                    api.put(
                        `/game/${route.params.id}/player/${nextPlayersTurn.value}/turn`
                    );
                }
            })
            .finally(() => {
                submittingTurn.value = false;
            });
    } catch (error) {
        console.error("Error submitting move:", error);
    }
};

const activePlayerIndexToPlayerId = (index) => {
    return players.value[index].id;
};
const activePlayerIdToIndex = (id) => {
    console.log("Mapping id to index", id, players.value);
    return players.value.findIndex((p) => p.id === id);
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
                v-if="isMyTurn && !submittingTurn"
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
            <div
                v-if="isMyTurn && !submittingTurn"
                class="flex flex-col items-center mb-6"
            >
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
                            !isMyTurn || submittingTurn
                                ? 'opacity-50 cursor-not-allowed'
                                : '', // Restrict interaction
                        ]"
                        @dragover.prevent
                        @drop="
                            isMyTurn && !submittingTurn
                                ? placeTile($event, rowIndex, colIndex)
                                : null
                        "
                        @click="
                            isMyTurn && !submittingTurn
                                ? removeTile(rowIndex, colIndex)
                                : null
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
                v-if="isMyTurn && !submittingTurn"
                class="cursor-pointer mt-6 px-6 py-2 bg-blue-500 text-white font-bold rounded shadow hover:bg-blue-700 transition"
                @click="submitWord"
            >
                Submit Word
            </button>
            <Loader v-show="submittingTurn" />
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
            <hr class="mt-6 mb-6" />
            <!-- Current Turn Words Display -->
            <div>
                <h3 class="text-lg font-bold text-gray-700 border-b pb-2">
                    Words This Turn
                </h3>
                <ul v-if="currentTurnWords.length" class="mt-2 space-y-4">
                    <li
                        v-for="word in currentTurnWords"
                        :key="word.wordString"
                        class="p-3 bg-blue-100 rounded-md shadow-sm"
                    >
                        <div class="flex justify-between items-center">
                            <strong class="text-blue-700 text-lg">{{
                                word.wordString
                            }}</strong>
                            <span class="text-green-600 font-bold"
                                >{{ word.wordScore }} pts</span
                            >
                        </div>

                        <!-- Tile Details -->
                        <ul class="mt-2 space-y-1 text-sm text-gray-700">
                            <li
                                v-for="tile in word.tiles"
                                :key="tile.tileId"
                                class="flex justify-between items-center p-2 bg-white rounded-md shadow-sm"
                            >
                                <span class="font-semibold text-gray-900">{{
                                    tile.letter
                                }}</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-blue-500 font-medium"
                                        >{{ tile.totalTileScore }} pts</span
                                    >
                                    <span
                                        v-if="tile.letterBonuses.length"
                                        class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded-md"
                                    >
                                        {{ tile.letterBonuses.join(", ") }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
                <p v-else class="text-gray-500 italic mt-2">
                    No words played this turn.
                </p>
            </div>
        </div>
    </div>
</template>
