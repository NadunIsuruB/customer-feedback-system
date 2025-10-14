#Product Action Triggers

DELIMITER $$

CREATE TRIGGER trg_products_ai AFTER INSERT ON products
FOR EACH ROW
BEGIN
  INSERT INTO audit_log (entity_type, entity_id, action, details, actor_id)
  VALUES ('product', NEW.product_id, 'create',
          CONCAT('{',
                 '"name":"', NEW.name, '",',
                 '"category":"', NEW.category, '",',
                 '"price_cents":', NEW.price_cents,
                 '}'),
          @actor_id);
END$$

CREATE TRIGGER trg_products_au AFTER UPDATE ON products
FOR EACH ROW
BEGIN
  INSERT INTO audit_log (entity_type, entity_id, action, details, actor_id)
  VALUES ('product', NEW.product_id, 'update',
          CONCAT('{',
            IF(OLD.name<>NEW.name, CONCAT('"name_from":"', OLD.name, '","name_to":"', NEW.name, '",'), ''),
            IF(OLD.category<>NEW.category, CONCAT('"category_from":"', OLD.category, '","category_to":"', NEW.category, '",'), ''),
            IF(OLD.price_cents<>NEW.price_cents, CONCAT('"price_from":', OLD.price_cents, ',"price_to":', NEW.price_cents, ','), ''),
            '"changed":true',
          '}'),
          @actor_id);
END$$

CREATE TRIGGER trg_products_ad AFTER DELETE ON products
FOR EACH ROW
BEGIN
  INSERT INTO audit_log (entity_type, entity_id, action, details, actor_id)
  VALUES ('product', OLD.product_id, 'delete',
          CONCAT('{"name":"', OLD.name, '"}'),
          @actor_id);
END$$

DELIMITER ;

#Feedback Action Triggers

DELIMITER $$

CREATE TRIGGER trg_feedback_ai AFTER INSERT ON feedback
FOR EACH ROW
BEGIN
  INSERT INTO audit_log (entity_type, entity_id, action, details, actor_id)
  VALUES ('feedback', NEW.feedback_id, 'create',
          CONCAT('{',
                 '"product_id":', NEW.product_id, ',',
                 '"customer_id":', NEW.customer_id, ',',
                 '"rating":', NEW.rating, ',',
                 '"state":"', NEW.state, '"',
                 '}'),
          @actor_id);
END$$

CREATE TRIGGER trg_feedback_au AFTER UPDATE ON feedback
FOR EACH ROW
BEGIN
  IF (OLD.state <> NEW.state) THEN
    INSERT INTO audit_log (entity_type, entity_id, action, details, actor_id)
    VALUES ('feedback', NEW.feedback_id,
            CASE WHEN NEW.state='Active' THEN 'approve' ELSE 'deactivate' END,
            CONCAT('{',
                   '"from":"', OLD.state, '",',
                   '"to":"', NEW.state, '"',
                   '}'),
            @actor_id);
  ELSE
    INSERT INTO audit_log (entity_type, entity_id, action, details, actor_id)
    VALUES ('feedback', NEW.feedback_id, 'update',
            CONCAT('{',
                   IF(OLD.rating<>NEW.rating, CONCAT('"rating_from":', OLD.rating, ',"rating_to":', NEW.rating, ','), ''),
                   IF(OLD.comment<>NEW.comment, '"comment_changed":true,', ''),
                   '"changed":true',
                   '}'),
            @actor_id);
  END IF;
END$$

CREATE TRIGGER trg_feedback_ad AFTER DELETE ON feedback
FOR EACH ROW
BEGIN
  INSERT INTO audit_log (entity_type, entity_id, action, details, actor_id)
  VALUES ('feedback', OLD.feedback_id, 'delete',
          CONCAT('{',
                 '"product_id":', OLD.product_id, ',',
                 '"rating":', OLD.rating,
                 '}'),
          @actor_id);
END$$

DELIMITER ;
