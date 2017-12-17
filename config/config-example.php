<?php
namespace CameraLife;

Models\Database::$dsn = 'sqlite:config/cameralife.db';
Models\Database::$username = 'user';
Models\Database::$password = 'pass';
Models\Database::$prefix = '';
Models\Database::$adminAccessCodeHash = '';
//Models\Database::$schemaVersion = 0; <-- only needed for MySQL connections
