// MonkeyPaw Chatbot Logic

class MonkeyPawChatbot {
    constructor() {
        this.chatMessages = document.getElementById('chatMessages');
        this.chatInput = document.getElementById('chatInput');
        this.sendButton = document.getElementById('sendButton');
        
        this.responses = {
            greetings: [
                "Hello! Ready to begin your MonkeyPaw adventure?",
                "Welcome! What brings you to the mysterious world of MonkeyPaw?",
                "Greetings, traveler! Are you prepared for what lies ahead?"
            ],
            start: [
                "Excellent! Let's begin your journey. What's your name?",
                "Perfect! First, tell me your name so I can guide you properly.",
                "Great! I need to know your name before we start this adventure."
            ],
            default: [
                "That's interesting. Tell me more about what you'd like to do.",
                "I see. How would you like to proceed?",
                "Intriguing choice. What's your next move?"
            ]
        };

        this.init();
    }

    init() {
        this.setupEventListeners();
        console.log('MonkeyPaw chatbot initialized');
    }

    setupEventListeners() {
        // Send message on button click
        this.sendButton.addEventListener('click', () => {
            this.sendMessage();
        });

        // Send message on Enter key press
        this.chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Listen for game events
        window.addEventListener('gameStarted', (event) => {
            this.handleGameStarted(event.detail);
        });

        window.addEventListener('choiceMade', (event) => {
            this.handleChoiceMade(event.detail);
        });
    }

    sendMessage() {
        const message = this.chatInput.value.trim();
        if (message === '') return;

        // Add user message to chat
        this.addMessage(message, 'user');
        
        // Clear input
        this.chatInput.value = '';

        // Process message and respond
        setTimeout(() => {
            this.processUserMessage(message);
        }, 500);
    }

    addMessage(content, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const messageContent = document.createElement('span');
        messageContent.className = 'message-content';
        messageContent.textContent = content;
        
        messageDiv.appendChild(messageContent);
        this.chatMessages.appendChild(messageDiv);

        // Scroll to bottom
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }

    processUserMessage(message) {
        const lowerMessage = message.toLowerCase();
        let response = '';

        // Simple keyword-based responses (to be enhanced with AI later)
        if (this.containsKeywords(lowerMessage, ['hello', 'hi', 'hey', 'start', 'begin'])) {
            response = this.getRandomResponse('greetings');
        } else if (this.containsKeywords(lowerMessage, ['yes', 'sure', 'ok', 'ready', 'let\'s go'])) {
            response = this.getRandomResponse('start');
        } else if (this.containsKeywords(lowerMessage, ['my name is', 'i am', 'i\'m', 'call me'])) {
            const name = this.extractName(message);
            if (name && window.monkeyPawGame) {
                window.monkeyPawGame.startGame(name);
                response = `Nice to meet you, ${name}! Your MonkeyPaw adventure begins now. You find yourself standing before an ancient, mysterious paw...`;
            } else {
                response = "I didn't catch your name clearly. Could you tell me again?";
            }
        } else {
            response = this.getRandomResponse('default');
        }

        this.addMessage(response, 'bot');
    }

    containsKeywords(message, keywords) {
        return keywords.some(keyword => message.includes(keyword));
    }

    extractName(message) {
        // Simple name extraction - can be improved
        const patterns = [
            /my name is ([a-zA-Z]+)/i,
            /i am ([a-zA-Z]+)/i,
            /i'm ([a-zA-Z]+)/i,
            /call me ([a-zA-Z]+)/i
        ];

        for (const pattern of patterns) {
            const match = message.match(pattern);
            if (match) {
                return match[1];
            }
        }
        return null;
    }

    getRandomResponse(category) {
        const responses = this.responses[category];
        return responses[Math.floor(Math.random() * responses.length)];
    }

    handleGameStarted(detail) {
        console.log('Game started for player:', detail.playerName);
        // Additional logic when game starts
    }

    handleChoiceMade(detail) {
        console.log('Player made choice:', detail.choice);
        // Respond to player choices
        const responses = [
            "Interesting choice! Let's see what happens...",
            "Bold decision! The consequences unfold...",
            "A wise choice... or is it? Time will tell..."
        ];
        
        setTimeout(() => {
            this.addMessage(this.getRandomResponse('default'), 'bot');
        }, 1000);
    }
}

// Initialize the chatbot when the page loads
document.addEventListener('DOMContentLoaded', () => {
    window.monkeyPawChatbot = new MonkeyPawChatbot();
});
