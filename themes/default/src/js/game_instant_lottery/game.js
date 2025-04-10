class Game {
  constructor() {
    // API URL constants
    this.API_URL_GET_GAME_STATE = '/modules/game_instant_lottery/api/getGameState';
    this.API_URL_HANDLE_CELL_CLICK = '/modules/game_instant_lottery/api/handleCellClick';
    this.API_URL_CASH_OUT = '/modules/game_instant_lottery/api/cashOut';
    this.API_URL_START = '/modules/game_instant_lottery/api/start';

    // Game state variables
    this.board = null;
    this.totalBalance = 0;
    this.sessionBank = 0;
    this.extraTurns = 0;
    this.currentRow = 0;
    this.rows = 0;
    this.cols = 0;
    this.currentSessionId = null;
    this.isGameFinished = false;
    
    // Cell types configuration
    this.cellTypes = {
      skull: {
        image: '/themes/default/assets/game_instant_lottery/images/skull.png',
      },
      coins: {
        image: '/themes/default/assets/game_instant_lottery/images/gold_coin.png',
      },
      multiplier: {
        image: '/themes/default/assets/game_instant_lottery/images/multiplier.png',
      },
      extra_turn: {
        image: '/themes/default/assets/game_instant_lottery/images/extra_turn.png',
      },
      empty: {
        image: '/themes/default/assets/game_instant_lottery/images/empty.png',
      },
      bomb: {
        image: '/themes/default/assets/game_instant_lottery/images/bomb.png',
      },
    };

    this.init();
  }

  /**
   * Initializes the game, setting up event listeners and fetching the initial game state.
   */
  init() {
    this.updateBalance(this.totalBalance);
    const startGameButton = document.querySelector('#startGameButton');
    const cashOutButton = document.querySelector('#cashOutButton');
    if (startGameButton && cashOutButton) {
      startGameButton.addEventListener('click', () => {
        const rows = document.querySelector('#rows').value;
        const cols = document.querySelector('#cols').value;
        this.startGame(rows, cols);
      });
      cashOutButton.addEventListener('click', () => this.cashOut());
    }
  }

  /**
   * Fetches the current game state from the server.
   */
  async fetchGameState() {
    try {
      const response = await fetch(this.API_URL_GET_GAME_STATE);
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return await response.json();
    } catch (error) {
      console.error('Error fetching game state:', error);
      return null;
    }
  }

  /**
   * Starts a new game, sending a request to the server and updating the UI.
   * @param {number} rows - The number of rows for the game board.
   * @param {number} cols - The number of columns for the game board.
   */
  async startGame(rows, cols) {
    try {
        const response = await fetch(this.API_URL_START, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ rows: rows, cols: cols })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();

        this.board = data.board;
        this.currentSessionId = data.sessionId;
        this.totalBalance = data.totalBalance;
        this.sessionBank = data.sessionBank;
        this.extraTurns = data.extraTurns;
        this.currentRow = data.currentRow;
        this.rows = rows;
        this.cols = cols;

        this.updateBoard();
        this.updateGameInfo();
    } catch (error) {
        console.error('Error starting game:', error);
    }
  }

    /**
   * Sends a cell click event to the server.
   * @param {number} row - The row index of the clicked cell.
   * @param {number} col - The column index of the clicked cell.
   * @returns {Promise<any>} - A promise that resolves with the server's response data.
   */
    async sendCellClick(row, col) {
    try {
      const response = await fetch(this.API_URL_HANDLE_CELL_CLICK, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      if (!response.ok) {
          throw new Error('Network response was not ok');
      }

      return await response.json();
    } catch (error) {
        console.error('Error handling cell click:', error);
        return null;
    }
  }

    /**
     * Handles the event when a cell is clicked, sending the click to the server and updating the UI.
     * @param {number} row - The row index of the clicked cell.
     * @param {number} col - The column index of the clicked cell.
     */
  async handleCellClick(row, col) {
    if (!this.currentSessionId || this.isGameFinished) {
      return; 
    }

    const data = await this.sendCellClick(row, col);
    if (data) {
      this.board = data.board;
      this.totalBalance = data.totalBalance;
      this.sessionBank = data.sessionBank;
      this.extraTurns = data.extraTurns;
      this.currentRow = data.currentRow;
      this.isGameFinished = data.isGameFinished;

      this.updateBoard();
      this.updateGameInfo();

      if (this.isGameFinished) {
        this.displayGameOver(data.winMessage);
      }
    }
  }

  /**
   * Sends a cash out request to the server.
   * @returns {Promise<any>} - A promise that resolves with the server's response data.
   */
  async sendCashOut() {
    try {
      const response = await fetch(this.API_URL_CASH_OUT, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({ sessionId: this.currentSessionId }),
      });

      if (!response.ok) {
          throw new Error('Network response was not ok');
      }

      return await response.json();
    } catch (error) {
        console.error('Error cashing out:', error);
        return null;
    }
  }

  /**
     * Cashes out the current session, updating the balance and displaying the game over message.
     */
  async cashOut() {
    if (!this.currentSessionId || this.isGameFinished) return;
    const data = await this.sendCashOut();
    if (data) {
        this.updateBalance(data.totalBalance);
        this.displayGameOver(data.winMessage);
        this.isGameFinished = true;
    }
  }

  updateBoard() {
    const boardContainer = document.getElementById('gameBoard');
    boardContainer.innerHTML = '';

    if (!this.board) return;

    const visibleRowsCount = 5; // Number of rows to display at once
    let startRow = Math.max(0, this.currentRow - Math.floor(visibleRowsCount / 2));
    let endRow = Math.min(this.rows - 1, startRow + visibleRowsCount - 1);

    // If we are near the top, adjust startRow to show top rows
    if (endRow - startRow + 1 < visibleRowsCount) {
        startRow = Math.max(0, endRow - visibleRowsCount + 1);
    }

    // If we are near the bottom, adjust endRow to show bottom rows
    if (this.currentRow > this.rows - Math.ceil(visibleRowsCount / 2)){
        startRow = this.rows - visibleRowsCount;
    }
    
    const table = document.createElement('table');
    for (let row = startRow; row <= endRow; row++) {
        const tr = document.createElement('tr');
        for (let col = 0; col < this.cols; col++) {
            const td = document.createElement('td');
            const cell = this.board[row][col];
            if (row > this.currentRow) {
                // Rows above the current one
                td.textContent = '?';
                td.classList.add('cell-hidden');
                td.classList.remove('clickable');
            } else if (row < this.currentRow) {
                // Rows below the current one
                this.setCellTypes(td, cell.type);
                td.classList.remove('clickable');
                td.classList.remove('cell-hidden');
            } else {
                // Current row
                if (cell.isOpened) {
                    this.setCellTypes(td, cell.type);
                    td.classList.remove('clickable');
                    td.classList.remove('cell-hidden');
                } else {
                    td.textContent = '?';
                    td.classList.add('clickable');
                    td.classList.remove('cell-hidden');
                    td.addEventListener('click', () => this.handleCellClick(row, col));
                }
            }
            tr.appendChild(td);
        }
        table.appendChild(tr);
        //add class active to current row
        if (row === this.currentRow) {
            tr.classList.add('active-row');
        } else {
            tr.classList.remove('active-row');
        }
    }
    boardContainer.appendChild(table);
  }

  /**
     * Updates the game information panel with the current session data.
     */
  updateGameInfo() {
      const gameInfoElement = document.getElementById('gameInfo');
      if (gameInfoElement) {
          gameInfoElement.innerHTML = `
              <div>Current Row: ${this.currentRow + 1} / ${this.rows}</div>
              <div>Session Bank: ${this.sessionBank}</div>
              <div>Extra Turns: ${this.extraTurns}</div>
          `;
      }
  }

    /**
     * Displays the game over message and resets the game state.
     * @param {string} message - The game over message to display.
     */
  displayGameOver(message) {
      alert(message);
      this.currentSessionId = null;
      this.board = null;
      this.sessionBank = 0;
      this.extraTurns = 0;
      this.isGameFinished = false;
      this.updateBoard();
      this.updateGameInfo();
      this.updateBalance(this.totalBalance);
  }

    /**
     * Updates the user's balance displayed on the page.
     * @param {number} newBalance - The new balance to display.
     */
    updateBalance(newBalance) {
        this.totalBalance = newBalance;
        const balanceElement = document.getElementById('balance');
        if (balanceElement) {
            balanceElement.textContent = `Balance: ${this.totalBalance}`;
        }
    }

    /**
     * Sets the correct image for the given cell type.
     * @param {HTMLElement} td - The table cell element.
     * @param {string} cellType - The type of the cell (skull, coins, etc.).
     */
  setCellTypes(td, cellType) {
      const cellInfo = this.cellTypes[cellType];
      if (cellInfo) {
          const img = document.createElement('img');
          img.src = cellInfo.image;
          img.alt = cellType;
          td.innerHTML = '';
          td.appendChild(img);
      } else {
          td.textContent = cellType;
      }
    }
  }
}

const game = new Game();