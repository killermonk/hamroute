<?php

// Try to set our content type
if (!headers_sent())
	header('Content-Type: application/json');

echo json_encode($json_data);