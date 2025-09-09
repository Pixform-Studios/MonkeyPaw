// MonkeyPaw Game Logic

class MonkeyPawGame {
    constructor() {
        this.gameState = {
            started: false,
            currentScene: 'welcome',
            playerData: {
                name: '',
                choices: [],
                inventory: []
            }
        };
        
        this.gameDisplay = document.getElementById('gameDisplay');
        this.init();
    }

    init() {
        console.log('MonkeyPaw game initialized');
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Game-specific event listeners can be added here
        window.addEventListener('gameStateChange', (event) => {
            this.handleGameStateChange(event.detail);
        });
    }

    handleGameStateChange(newState) {
        this.gameState = { ...this.gameState, ...newState };
        this.updateDisplay();
    }

    updateDisplay() {
        // Update the game display based on current state
        if (this.gameState.currentScene === 'welcome') {
            this.showWelcomeScreen();
        }
    }

    showWelcomeScreen() {
        this.gameDisplay.innerHTML = `
            <div class="welcome-message">
                <h2>Welcome to MonkeyPaw</h2>
                <p>Your interactive adventure begins here...</p>
                <p class="game-hint">Start chatting with the bot to begin your journey!</p>
            </div>
        `;
    }

    startGame(playerName) {
        this.gameState.started = true;
        this.gameState.playerData.name = playerName;
        this.gameState.currentScene = 'intro';
        
        // Dispatch event to notify other components
        window.dispatchEvent(new CustomEvent('gameStarted', {
            detail: { playerName: playerName }
        }));

        this.updateDisplay();
    }

    makeChoice(choice) {
        this.gameState.playerData.choices.push(choice);
        
        // Process the choice and update game state
        this.processPlayerChoice(choice);
    }

    processPlayerChoice(choice) {
        // Game logic for processing player choices
        console.log('Player choice:', choice);
        
        // This will be expanded based on game requirements
        window.dispatchEvent(new CustomEvent('choiceMade', {
            detail: { choice: choice }
        }));
    }

    addToInventory(item) {
        this.gameState.playerData.inventory.push(item);
        console.log('Added to inventory:', item);
    }

    getGameState() {
        return this.gameState;
    }
}

// Initialize the game when the page loads
document.addEventListener('DOMContentLoaded', () => {
    window.monkeyPawGame = new MonkeyPawGame();
});
