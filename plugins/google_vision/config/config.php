<?php

$google_vision_api_key="";
$google_vision_label_field=73;
$google_vision_landmarks_field=1;
$google_vision_text_field=18;
$google_vision_restypes=array("1");
$google_vision_features=array("LABEL_DETECTION","LANDMARK_DETECTION","TEXT_DETECTION");
$google_vision_autotitle=false;
$google_vision_face_detect_field=0;
$google_vision_face_detect_verbose=false;
$google_vision_face_detect_fullface=true;
$google_vision_face_dependent_field=0;

// Add any new vars that specify metadata fields to this array to stop them being deleted if plugin is in use
// These are added in hooks/all.php
$google_vision_core_fields = array("google_vision_label_field",
    "google_vision_landmarks_field",
    "google_vision_text_field",
    "google_vision_face_detect_field",
    "google_vision_face_dependent_field");
