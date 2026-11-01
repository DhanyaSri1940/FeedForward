// Chatbot main interaction script

document.addEventListener("DOMContentLoaded", () => {
    const inputField = document.getElementById("input");
    inputField.addEventListener("keydown", (e) => {
        if (e.code === "Enter" || e.keyCode === 13) {
            let input = inputField.value;
            inputField.value = "";
            output(input);
        }
    });
});

function output(input) {
    let product;
    // Clean and normalize input
    let text = input
        .toLowerCase()
        .replace(/[^\w\s]/gi, "")
        .replace(/[\d]/gi, "")
        .trim()
        .replace(/ a /g, " ")
        .replace(/i feel /g, "")
        .replace(/whats/g, "what is")
        .replace(/please /g, "")
        .replace(/ please/g, "")
        .replace(/r u/g, "are you")
        .replace(/tell about /g, "");

    if (compare(prompts, replies, text)) {
        product = compare(prompts, replies, text);
    } else if (/thank/gi.test(text)) {
        product = "You're welcome!";
    } else if (/(corona|covid|virus)/gi.test(text)) {
        product = coronavirus[Math.floor(Math.random() * coronavirus.length)];
    } else {
        product = alternative[Math.floor(Math.random() * alternative.length)];
    }

    addChat(input, product);
}

function compare(promptsArray, repliesArray, string) {
    let reply;
    let replyFound = false;
    for (let x = 0; x < promptsArray.length; x++) {
        for (let y = 0; y < promptsArray[x].length; y++) {
            if (promptsArray[x][y] === string) {
                let replies = repliesArray[x];
                reply = replies[Math.floor(Math.random() * replies.length)];
                replyFound = true;
                break;
            }
        }
        if (replyFound) break;
    }
    return reply;
}

function addChat(input, product) {
    const messagesContainer = document.getElementById("messages");

    // User message
    let userDiv = document.createElement("div");
    userDiv.id = "user";
    userDiv.className = "user response";
    userDiv.innerHTML = `<img src="img/user.png" class="avatar"><span>${input}</span>`;
    messagesContainer.appendChild(userDiv);

    // Bot message (with typing...)
    let botDiv = document.createElement("div");
    let botImg = document.createElement("img");
    let botText = document.createElement("span");
    botDiv.id = "bot";
    botImg.src = "img/bot-mini.png";
    botImg.className = "avatar";
    botDiv.className = "bot response";
    botText.innerText = "Typing...";
    botDiv.appendChild(botText);
    botDiv.appendChild(botImg);
    messagesContainer.appendChild(botDiv);

    // Scroll to latest message
    messagesContainer.scrollTop = messagesContainer.scrollHeight - messagesContainer.clientHeight;

    // Simulate typing delay
    setTimeout(() => {
        botText.innerText = `${product}`;
        if (typeof textToSpeech === 'function') {
            textToSpeech(product);
        }
    }, 2000);
} 