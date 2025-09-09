# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

MonkeyPaw is an interactive web-based game based on the classic "Monkey's Paw" legend. Players make three wishes through a chat interface, and an AI-powered genie grants each wish with malicious, ironic twists. The game features visual finger animations on a monkey paw that close with each wish, creating a troll-style gaming experience that's both entertaining and cautionary.

## Development Commands

### Running the Application
The game requires a web server with PHP support for the Gemini API integration:

```bash
# For development with PHP built-in server
php -S localhost:8000
# Then visit http://localhost:8000

# Or open directly in browser (API features won't work)
start index.html

# For production deployment to Hostinger or similar PHP hosting
# Upload all files maintaining directory structure
```

### Development Workflow
```bash
# Frontend changes - direct file editing
# Simply refresh the browser after making changes to HTML/CSS/JS

# Backend API changes - edit api/gemini-proxy.php
# Restart PHP server if using built-in server

# Check browser console for JavaScript errors
# Check server logs for PHP/API errors
```

### Testing
```bash
# Manual testing workflow:
# 1. Open game in browser
# 2. Make test wishes (try both valid wishes and non-wishes)
# 3. Verify finger animations work
# 4. Test game restart functionality
# 5. Check responsive design on mobile

# Test API integration:
# - Make wishes and verify Gemini responses
# - Test fallback responses when API fails
# - Verify API key is not exposed in browser
```

## Code Architecture

### Core Structure
The application follows a modular architecture with secure API integration:

1. **Game Logic (`js/game.js`)**
   - `MonkeyPawGame` class manages wishes, finger animations, and game flow
   - Tracks wish count (0-3), handles paw visual states, and game over logic
   - Controls SVG finger animations and emoji fallbacks
   - Key events: `wishMade`, `wishProcessed`

2. **AI Integration (`js/chatbot.js` + `api/gemini-proxy.php`)**
   - `MonkeyPawChatbot` class handles wish processing and chat UI
   - Secure PHP proxy hides Gemini API key from client-side
   - Comprehensive prompt engineering for monkey paw game mechanics
   - Fallback responses when API is unavailable
   - Validates wishes vs. non-wishes and formats genie responses

3. **Visual Components**
   - **SVG Monkey Paw (`images/monkey-paw.svg`)**: Cyanide & Happiness style art with animation states
   - **CSS Animations**: Finger closing transitions, typing indicators, message animations
   - **Responsive Layout**: Vertical game layout optimized for the game flow

### Game Flow Architecture
1. Player makes wish → `wishMade` event
2. Chatbot processes through Gemini API → AI generates twisted response
3. `wishProcessed` event → Game closes finger animation
4. Repeat until 3 wishes → Game over screen with restart option

### API Security Design
- **Client-side**: No API keys exposed, uses local proxy endpoint
- **Server-side**: PHP proxy at `api/gemini-proxy.php` handles Gemini API calls
- **Error handling**: Graceful fallback to local responses when API fails
- **Validation**: Server-side input validation and response sanitization

### Key Data Structures
```javascript
// Game state structure
gameState = {
    wishesUsed: number,     // 0-3
    maxWishes: 3,
    gameOver: boolean,
    wishes: [               // Array of wish objects
        {
            text: string,
            wishNumber: number,
            timestamp: number
        }
    ]
}

// Gemini API payload
apiPayload = {
    wish: string,           // User's wish text
    wishNumber: number      // Which wish (1-3)
}
```

### Prompt Engineering Strategy
The system prompt is carefully designed to:
- Force literal/ironic interpretations of wishes
- Generate creative "catches" that subvert user expectations
- Maintain genie character voice (oblivious to malicious nature)
- Handle edge cases (non-wishes, inappropriate content)
- Keep responses concise but entertaining

### Development Patterns
- **Event-driven architecture**: Loose coupling between game and chatbot
- **Progressive enhancement**: Works with emoji fallback if SVG fails
- **Secure API design**: Never expose credentials to client
- **Responsive animations**: CSS animations with JavaScript triggers
- **Error resilience**: Multiple fallback layers for robust user experience

### File Structure
```
MonkeyPaw/
├── index.html          # Main HTML file
├── css/
│   └── styles.css      # All styling
├── js/
│   ├── game.js         # Game logic and state management
│   └── chatbot.js      # Chat interface and NLP
└── README.md          # Project documentation
```

### Browser Compatibility
- Modern browsers (ES6+ support required)
- Uses vanilla JavaScript (no framework dependencies)
- CSS Grid and Flexbox for layout
- No transpilation currently configured
