import os
import sys
import logging
import json
from groq import Groq

os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"
logging.getLogger("absl").setLevel(logging.CRITICAL)

API_KEY = "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"  

client = Groq(api_key=API_KEY)

def generate_health_advice(risk_factors_json):
    try:
        risk_factors = json.loads(risk_factors_json)
    except Exception as e:
        return f"Error parsing risk factors: {str(e)}"

    increasing_factors = risk_factors

    if not increasing_factors:
        return "Your profile currently doesn't have any factors significantly increasing your immediate risk. Keep maintaining a healthy lifestyle!"

    factors_str = "\n".join([f"- {f['name']} (Current Value: {f['value']})" for f in increasing_factors])

    prompt = f"""
You are a professional medical advisor.

The following modifiable factors increase this person's stroke risk:
{factors_str}

Provide:
- Short encouraging introduction
- Brief explanation per factor + immediate goal
- personalized advices based on the factors
- A short bold medical disclaimer
-try to not provide the same answer every time

Use clean Markdown formatting.
Be professional and empathetic.
Return only formatted text add emojis and don't use special charchters like * or #.
"""

    try:
        response = client.chat.completions.create(
            model="openai/gpt-oss-120b",  
            messages=[{"role": "user", "content": prompt}],
            max_tokens=1200,
            temperature=0.3,
        )
        content = response.choices[0].message.content
        return content if content else "No valid content returned."

    except Exception as e:
        err = str(e)
        if "401" in err or "invalid_api_key" in err or "authentication" in err.lower():
            return "cle API invalide ou expirée. Veuillez mettre à jour l'API_KEY dans AI_advice.py."
        elif "429" in err or "rate_limit" in err.lower():
            return "Limite de l'API Groq atteinte. Veuillez réessayer plus tard."
        return f"Erreur lors de la génération des conseils: {str(e)}"

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Error: Missing input argument (risk factors JSON).")
        sys.exit(1)

    risk_factors_input = sys.argv[1]
    print(generate_health_advice(risk_factors_input))