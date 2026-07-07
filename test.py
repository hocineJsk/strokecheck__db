import sys
import joblib
import pandas as pd
import json

import warnings
warnings.filterwarnings('ignore')

try:
    import shap
    SHAP_AVAILABLE = True
except ImportError:
    SHAP_AVAILABLE = False

import os
script_dir = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(script_dir, "WEB_stroke_random_forest_model_WEB.joblib")
scaler_path = os.path.join(script_dir, "WEB_scaler_of_stroke_RF_THIS_IS_CORRECT_VER_WEB.joblib")

model = joblib.load(model_path)
scaler = joblib.load(scaler_path)

if len(sys.argv) < 16:
    print(json.dumps({'error': 'Missing arguments'}))
    sys.exit(1)

try:
    args = [float(x) for x in sys.argv[1:16]]
except ValueError as e:
    print(json.dumps({'error': f'Invalid input: {str(e)}'}))
    sys.exit(1)

feature_names = [
    'age',
    'avg_glucose_level',
    'bmi',
    'gender_Male',
    'hypertension_1',
    'heart_disease_1',
    'ever_married_Yes',
    'work_type_Never_worked',
    'work_type_Private',
    'work_type_Self-employed',
    'work_type_children',
    'Residence_type_Urban',
    'smoking_status_formerly smoked',
    'smoking_status_never smoked',
    'smoking_status_smokes'
]

#Noms des features
feature_display_names = {
    'age': 'Age',
    'avg_glucose_level': 'Glucose Level',
    'bmi': 'BMI',
    'gender_Male': 'Male Gender',
    'hypertension_1': 'Hypertension',
    'heart_disease_1': 'Heart Disease',
    'ever_married_Yes': 'Married Status',
    'work_type_Never_worked': 'Never Worked',
    'work_type_Private': 'Private Sector Work',
    'work_type_Self-employed': 'Self-employed',
    'work_type_children': 'Child',
    'Residence_type_Urban': 'Urban Residence',
    'smoking_status_formerly smoked': 'Former Smoker',
    'smoking_status_never smoked': 'Never Smoked',
    'smoking_status_smokes': 'Current Smoker'
}

#Build DataFrame
df = pd.DataFrame([args], columns=feature_names)

try:
    pred = model.predict(df)[0]
    prob = model.predict_proba(df)[0][1]
except Exception as e:
    print(json.dumps({'error': f'Prediction error: {str(e)}'}))
    sys.exit(1)

#SHAP explication
top_features = []

if SHAP_AVAILABLE:
    try:
        explainer = shap.TreeExplainer(model)
        shap_values = explainer.shap_values(df)
        
        import numpy as np
        shap_array = np.array(shap_values)
        if len(shap_array.shape) == 3:
            shap_values_class1 = shap_array[0, :, 1]
        elif isinstance(shap_values, list) and len(shap_values) == 2:
            shap_values_class1 = shap_values[1][0]
        else:
            shap_values_class1 = shap_values[0]
     
        feature_impacts = []
        for i, fname in enumerate(feature_names):
            try:
                # Extraire la valeur SHAP pour cette feature
                shap_val = float(shap_values_class1[i])
                
                # Ne garder que les features avec un impact significatif
                if abs(shap_val) > 0.001:
                    feature_impacts.append({
                        'name': feature_display_names.get(fname, fname),
                        'value': float(args[i]),
                        'shap_value': shap_val,
                        'impact': 'increases' if shap_val > 0 else 'decreases'
                    })
            except (IndexError, TypeError) as e:
                continue
        
        feature_impacts.sort(key=lambda x: abs(x['shap_value']), reverse=True)
        
        top_features = feature_impacts[:5]
        
        #Collect ALL features where risk increases, excluding 'age'
        increasing_risk_factors = [
            f for f in feature_impacts 
            if f['shap_value'] > 0 and f['name'] != 'Age'
        ]
        
    except Exception as e:
        pass

# output pour PHP JSON format
result = {
    'probability': float(prob),
    'prediction': int(pred),
    'top_features': top_features,
    'increasing_risk_factors': increasing_risk_factors
}

print(json.dumps(result))
