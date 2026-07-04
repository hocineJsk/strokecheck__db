import json
from groq import Groq

API_KEY = "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" 

client = Groq(api_key=API_KEY)

print("=" * 40)
print("TEST 1 : Connexion API")
print("=" * 40)
try:
    response = client.chat.completions.create(
        model="llama-3.3-70b-versatile",
        messages=[{"role": "user", "content": "Say hello in one sentence."}],
        max_tokens=50,
    )
    print("✅ API fonctionne !")
    print("Réponse :", response.choices[0].message.content)
except Exception as e:
    print("❌ Erreur :", str(e))

print("\n" + "=" * 40)
print("TEST 2 : generate_health_advice")
print("=" * 40)

from AI_advice import generate_health_advice  

test_factors = json.dumps([
    {"name": "Hypertension", "value": "High (145/95 mmHg)"},
    {"name": "BMI", "value": "29.5 (Overweight)"},
    {"name": "Smoking", "value": "Yes"}
])

result = generate_health_advice(test_factors)
print("✅ Résultat :\n")
print(result)

print("\n" + "=" * 40)
print("TEST 3 : Liste vide")
print("=" * 40)
result_empty = generate_health_advice(json.dumps([]))
print("✅ Résultat :", result_empty)

print("\n" + "=" * 40)
print("TEST 4 : JSON invalide")
print("=" * 40)
result_invalid = generate_health_advice("not a valid json")
print("✅ Résultat :", result_invalid)