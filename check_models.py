import google.generativeai as genai
import os

API_KEY = "AIzaSyB0m66lzTcUzX3SBdpWM6k6QeK3vykxPc8"
genai.configure(api_key=API_KEY)

try:
    for m in genai.list_models():
        if 'generateContent' in m.supported_generation_methods:
            print(m.name)
except Exception as e:
    print(f"Error: {e}")
