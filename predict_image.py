import os
import sys
import json
import numpy as np
from PIL import Image

os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"
import tensorflow as tf
tf.get_logger().setLevel('ERROR')
import warnings
warnings.filterwarnings('ignore')

def predict_stroke_image(image_path, model_path):
    try:
        # Load model without compiling (to avoid optimizer/loss issues)
        model = tf.keras.models.load_model(model_path, compile=False)
        
        # Preprocess Image
        # Based on model.input_shape: (None, 224, 224, 3)
        img = Image.open(image_path).convert('RGB')
        img = img.resize((224, 224))
        img_array = np.array(img) / 255.0  # Normalize to [0, 1]
        img_array = np.expand_dims(img_array, axis=0) # Batch dimension
        
        # Inference
        predictions = model.predict(img_array, verbose=0)
        
        # Handle multi-output model: [(None, 224, 224, 1), (None, 3)]
        # Branch 1 is the classification result (index 1 in the list)
        if isinstance(predictions, list):
            class_probs = predictions[1][0]
        else:
            class_probs = predictions[0]

        # Classes (Assuming standard order for these datasets)
        classes = ["Normal", "Ischemic Stroke", "Hemorrhagic Stroke"]
        predicted_idx = np.argmax(class_probs)
        confidence = float(class_probs[predicted_idx])
        
        result = {
            "prediction": classes[predicted_idx],
            "confidence": confidence,
            "probabilities": {classes[i]: float(class_probs[i]) for i in range(len(classes))},
            "status": "success"
        }
        return json.dumps(result)

    except Exception as e:
        return json.dumps({"status": "error", "message": str(e)})

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "No image path provided."}))
        sys.exit(1)
    
    img_path = sys.argv[1]
    mod_path = "/home/hocine/brain_stroke_web/brain_stroke_model (1).h5"
    print(predict_stroke_image(img_path, mod_path))
