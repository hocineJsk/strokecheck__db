from google import genai
import os

API_KEY = "AIzaSyB0ahRkrByu8KQ8d3J-28T8oSb_N_XqU2o"
client = genai.Client(api_key=API_KEY)

print("Listing models...")
for model in client.models.list():
    print(f"Model: {model.name}, Supported methods: {model.supported_generation_methods}")
