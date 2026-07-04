import joblib
import pandas as pd
import shap
import numpy as np

# Load model
model = joblib.load("/home/hocine/Documents/brain_stroke_web/WEB_stroke_random_forest_model_WEB.joblib")

# Test data
args = [65, 200, 30, 1, 1, 1, 1, 0, 1, 0, 0, 1, 0, 0, 1]

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

df = pd.DataFrame([args], columns=feature_names)

print("=" * 60)
print("DIAGNOSTIC SHAP")
print("=" * 60)

# Prediction
prob = model.predict_proba(df)[0][1]
print(f"\n✓ Probabilité de stroke: {prob*100:.2f}%")

# SHAP values
print("\nAnalyse SHAP:")
explainer = shap.TreeExplainer(model)
shap_values = explainer.shap_values(df)

print(f"Type de shap_values: {type(shap_values)}")

if isinstance(shap_values, list):
    print(f"Nombre d'éléments dans la liste: {len(shap_values)}")
    for i, sv in enumerate(shap_values):
        print(f"  Classe {i}: shape = {np.array(sv).shape}, type = {type(sv)}")
    
    # Extraire les valeurs pour la classe 1 (stroke)
    shap_class1 = shap_values[1]
    print(f"\nValeurs SHAP pour classe 1 (stroke):")
    print(f"  Shape: {np.array(shap_class1).shape}")
    print(f"  Première ligne: {shap_class1[0]}")
    
    # Afficher les top features
    print("\n TOP 5 FEATURES:")
    shap_vals = shap_class1[0]
    feature_importance = [(feature_names[i], shap_vals[i], args[i]) 
                          for i in range(len(feature_names))]
    feature_importance.sort(key=lambda x: abs(x[1]), reverse=True)
    
    for i, (name, shap_val, value) in enumerate(feature_importance[:5], 1):
        impact = "↑ AUGMENTE" if shap_val > 0 else "↓ DIMINUE"
        print(f"{i}. {name:30} = {value:6.2f}  |  SHAP: {shap_val:+.4f}  {impact}")
else:
    shap_array = np.array(shap_values)
    print(f"Shape: {shap_array.shape}")
    
    # Pour le format (1, 15, 2)
    if len(shap_array.shape) == 3:
        print(f"\n Format détecté: (n_samples, n_features, n_classes)")
        print(f"  Échantillons: {shap_array.shape[0]}")
        print(f"  Features: {shap_array.shape[1]}")
        print(f"  Classes: {shap_array.shape[2]}")
        
        # Extraire les valeurs pour la classe 1 (colonne 1)
        shap_vals = shap_array[0, :, 1]
        
        print("\n TOP 5 FEATURES (pour classe 1 - stroke):")
        feature_importance = [(feature_names[i], shap_vals[i], args[i]) 
                              for i in range(len(feature_names))]
        feature_importance.sort(key=lambda x: abs(x[1]), reverse=True)
        
        for i, (name, shap_val, value) in enumerate(feature_importance[:5], 1):
            impact = "↑ AUGMENTE" if shap_val > 0 else "↓ DIMINUE"
            print(f"{i}. {name:30} = {value:6.2f}  |  SHAP: {shap_val:+.4f}  {impact}")
    else:
        print(f"Contenu: {shap_values}")

print("\n" + "=" * 60)