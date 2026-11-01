// Chatbot prompt/response constants

const date = new Date();
const currentdate = new Date().toLocaleDateString();
const time = new Date().toLocaleTimeString();

// User input prompts
const prompts = [
  // Greetings
  ["hi", "hello", "hey", "hai", "good morning", "good afternoon", "good evening"],

  // How are you
  ["how are you", "how r u", "how are things", "how is it going"],

  // Identity
  ["who are you", "what are you", "are you a bot", "are you human"],
  ["your name", "name please", "what is your name", "who am i talking to"],

  // Creator
  ["who created you", "who made you", "who developed you"],

  // Project related
  ["project", "food waste", "food waste management", "feed forward", "foodbuddy", "system"],

  // Donation general
  ["donate", "donation", "food donate", "donate food", "give food"],
  ["what can i donate", "types of donation", "what food"],

  // Cooked / Raw food
  ["cooked food", "can i donate cooked food", "homemade food"],
  ["raw food", "raw ingredients", "uncooked food"],

  // Packaging & safety
  ["package", "packaging", "how to pack", "store food", "food safety"],
  ["guidelines", "rules", "instructions", "precautions"],

  // Usage of donation
  ["how my donation is used", "where food goes", "who receives food"],

  // NGOs & location
  ["ngo", "trust", "orphanage", "chikkaballapur", "nearby ngos"],

  // Date & time
  ["date", "today date"],
  ["time", "current time"],

  // Help
  ["help", "support", "guide me", "how can you help"],

  // Emotions
  ["happy", "good", "fine", "great", "awesome"],
  ["sad", "bad", "tired", "bored"],

  // Fun
  ["joke", "funny", "make me laugh", "haha", "lol"],
  ["story", "tell me a story"],

  // Thanks
  ["thank you", "thanks", "thx"],

  // Goodbye
  ["bye", "goodbye", "see you", "see you later"],

  // Yes / No
  ["yes", "yeah", "ok", "okay"],
  ["no", "not now", "maybe later"]
];


// Possible responses, in corresponding order
const replies = [
  ["Hello! Welcome to FoodBuddy 😊"],

  ["I’m doing great! How can I help you today?"],

  ["I am FoodBuddy 🤖, a food donation assistant."],
  ["My name is FoodBuddy."],

  ["I was created by student of SJC Institute of Technology -Dhanya Sri S."],

  ["This project collects excess food and distributes it to people in need."],

  ["You can donate food easily through this system."],
  ["You can donate raw, cooked, or packed food."],

  ["Yes, cooked food can be donated if it is fresh and hygienic."],
  ["Raw food should be unopened and unexpired."],

  ["Please pack food in clean, airtight containers and label it properly."],
  ["Follow hygiene, freshness, and proper packaging guidelines."],

  ["Your donation helps feed needy people through trusted organizations."],

  ["We work with trusted NGOs and volunteers in Chikkaballapur."],

  [currentdate],
  [time],

  ["I can help you with food donation, guidelines, NGOs, and project info."],

  ["That’s great to hear 😊"],
  ["I’m here to help. Everything will be okay."],

  ["Why did the food donate itself? Because it had a big heart 😄"],
  ["Once upon a time… food saved the day!"],

  ["You’re welcome! 😊"],

  ["Goodbye! Thanks for helping reduce food waste 🙏"],

  ["Okay 👍"],
  ["No problem 😊"]
];


// Random for any other user input
const alternative = [
  "I can help with food donation, guidelines, NGOs, date, time, or project details 😊",
  "Please ask me about donating food or the Food Waste Management project.",
  "I’m here to help you reduce food waste 🌍"
];


