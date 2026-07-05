<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $age = $_POST["age"];
    $glucose = $_POST["glucose"] * 100;
    
    $height = $_POST["height"];
    $weight = $_POST["weight"];
    $bmi = round($weight / (($height / 100) ** 2), 2);

    $gender = $_POST["gender"];
    $residence = $_POST["residence"];
    $hypertension = $_POST["hypertension"];
    $disease = $_POST["disease"];
    $married = $_POST["married"];
    $work = $_POST["work"];
    $smoking = $_POST["smoking"];
    
    $gender_Male          = ($gender == "1") ? 1 : 0;
    $Residence_type_Urban = ($residence == "1") ? 1 : 0;
    $wt_never             = ($work == "0") ? 1 : 0;
    $wt_private           = ($work == "1") ? 1 : 0;
    $wt_self              = ($work == "2") ? 1 : 0;
    $wt_children          = ($work == "3") ? 1 : 0;
    $sm_former            = ($smoking == "1") ? 1 : 0;
    $sm_never             = ($smoking == "2") ? 1 : 0;
    $sm_smokes            = ($smoking == "3") ? 1 : 0;

    $feature_array = [
        $age, $glucose, $bmi, $gender_Male, $hypertension, $disease, $married, 
        $wt_never, $wt_private, $wt_self, $wt_children, $Residence_type_Urban, 
        $sm_former, $sm_never, $sm_smokes
    ];
    
    $escaped_args = implode(" ", array_map("escapeshellarg", $feature_array));
    $python_path = __DIR__ . "/venv/bin/python";
    if (!file_exists($python_path)) {
        $python_path = "python3";
    }

    $predict_cmd = "$python_path test.py $escaped_args 2>&1";
    $prediction_json_raw = shell_exec($predict_cmd);
 
    $prediction_data = json_decode($prediction_json_raw, true);

    $ai_text = null;
    $action = $_POST["action"] ?? "predict";

    if ($action === "advice" && $prediction_data && isset($prediction_data['increasing_risk_factors'])) {
        $features_for_ai = json_encode($prediction_data['increasing_risk_factors']);
        $escaped_json = escapeshellarg($features_for_ai);
        
        $advice_cmd = "$python_path AI_advice.py $escaped_json 2>&1";
        $ai_text = shell_exec($advice_cmd);
    } elseif ($action === "advice") {
        if (!$prediction_data) {
            $ai_text = "Debug - test.py output was not valid JSON: " . $prediction_json_raw;
        } else {
            $ai_text = "Sorry the AI can't generate any advice (No risk factors found).";
        }
    }

    if ($action === "advice" && !$ai_text) {
        $ai_text = "Aucune réponse de l'IA conseils.";
    }
    
    $redirect = "index.php?prediction=" . urlencode($prediction_json_raw);
    if ($ai_text !== null) {
        $redirect .= "&response=" . urlencode($ai_text);
    }
    header("Location: " . $redirect);
    exit();
}
?>
