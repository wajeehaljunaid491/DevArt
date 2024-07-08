const express = require('express');
const bodyParser = require('body-parser');
const Groq = require('groq-sdk'); // Ensure you have installed groq-sdk
const cors = require('cors');
require('dotenv').config(); // Ensure you have dotenv installed to use environment variables

const app = express();
const groq = new Groq({ apiKey: process.env.API_KEY });

app.use(cors()); // Use the cors middleware

app.use(bodyParser.json());

// Function to get chat completion from Groq
async function getGroqChatCompletion(quizData) {
  return groq.chat.completions.create({
    messages: [
      {
        role: "user",
        content: `Process the following quiz data and generate a personalized result:
        Questions: ${quizData.questions.join('\n')}
        Answers: ${JSON.stringify(quizData.answers)}. generate the just the answer`
      },
    ],
    model: "llama3-8b-8192",
  });
}

// Handle quiz submission
app.post('/submit-quiz', async (req, res) => {
  try {
    const quizData = req.body;
    const chatCompletion = await getGroqChatCompletion(quizData);
    const resultMessage = chatCompletion.choices[0]?.message?.content || "No result generated.";
    res.json({ message: resultMessage });
  } catch (error) {
    console.error('Error processing quiz:', error);
    res.status(500).json({ message: "Error processing quiz" });
  }
});

// Define the port to listen on
const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});
