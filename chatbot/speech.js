// Text to Speech utility for chatbot

const synth = window.speechSynthesis;

function textToSpeech(text) {
  if (!synth) return;
  const utterance = new SpeechSynthesisUtterance(text);
  utterance.lang = "en-US";
  utterance.volume = 1;
  utterance.rate = 1;
  utterance.pitch = 1; // 0 (lowest) to 2 (highest)
  synth.speak(utterance);
} 