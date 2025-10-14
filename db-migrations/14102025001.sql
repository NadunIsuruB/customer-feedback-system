CREATE TABLE audit_log (
  log_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type  ENUM('product','feedback') NOT NULL,
  entity_id    INT UNSIGNED NOT NULL,
  action       ENUM('create','update','delete','approve','deactivate') NOT NULL,
  details      TEXT NULL,                         -- JSON-ish text, short summary
  actor_id     INT UNSIGNED NULL,                 -- filled via @actor_id from app if present
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (entity_type, entity_id),
  KEY (action),
  KEY (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;