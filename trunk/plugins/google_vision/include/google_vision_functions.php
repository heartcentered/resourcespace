<?php

function google_visionProcess($resource)
    {
    global $google_vision_api_key,$google_vision_label_field;
    
    # API URL
    $url="https://vision.googleapis.com/v1/images:annotate?key=" . $google_vision_api_key;
    
    # Find a suitable file
    $file=get_resource_path($resource,true,"scr"); # Try screen size preview.
    if (!file_exists($file)) {$file=get_resource_path($resource,true,"pre");} # Try a smaller preview.
    if (!file_exists($file)) {return false;} # No suitable size.
    
    # Fetch and encode the file.
    $data = file_get_contents($file);
    $base64 = base64_encode($data);
                                
    # Form the JSON request.
    $request='{
      "requests": [
        {
          "image": {
            "content": "' . $base64 . '"
          },
          "features": [
            {
              "type": "LABEL_DETECTION"
            }
          ]
        }
      ]
    }';
    
    #echo $request;
    
    # Build a HTTP request, and fetch results.
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/json',
            'content' => $request
        )
    );
    $context  = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    
    /*
     * Alternative CURL code if preferred or required at some future stage....
     * 
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    */
    
    $result=json_decode($result,true); # Parse and return as associative arrays
    
    #echo "<pre>";
    #print_r($result);
    #echo "</pre>";
    
    $nodes=array();
    
    if (isset($result["responses"][0]["labelAnnotations"]))      
        {
        # Keywords found. Loop through them and resolve node IDs for each, or add new nodes if no matching node exists.
        foreach ($result["responses"][0]["labelAnnotations"] as $label)
            {
            # Create new or fetch existing node
            $nodes[]=set_node(null, $google_vision_label_field, ucfirst($label["description"]), null, 9999,true);  #set_node($ref, $resource_type_field, $name, $parent, $order_by,$returnexisting=false)
            #echo $label["description"] . "/";
            }
                
        add_resource_nodes($resource,$nodes);
        #print_r($nodes);
        return true;
        }
    else
        {
        return false;
        }
    }