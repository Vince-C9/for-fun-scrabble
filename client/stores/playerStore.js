import { defineStore } from 'pinia'

export const usePlayerStore = defineStore('player', {
  state: () => ({
    games: [],
  }),
  actions: {
    joinGame(gameId, playerId) {

      if (!this.isInGame(gameId)) {
        this.games.push({ gameId, playerId })
        this.saveToLocalStorage()
      }
    },

    leaveGame(gameId) {
      this.games = this.games.filter(game => game.gameId !== gameId)
      this.saveToLocalStorage()
    },

    saveToLocalStorage() {
        localStorage.setItem('games', JSON.stringify(this.games))
    },

    getGamesFromLocalStorage() {
      let storedGames = JSON.parse(localStorage.getItem('games'))
      if (storedGames) {
        this.games = storedGames
      }else{
        console.log("No games stored");
      }

    },
    isInGame(gameId) {
      return this.games.some(game => game.gameId === gameId)
    }
  },
   

})