# Table definitions

```sql
CREATE TABLE search (
  search_id      INT(11) UNSIGNED PRIMARY KEY          NOT NULL,
  user_id        INT(11),
  search_date    TIMESTAMP DEFAULT 'CURRENT_TIMESTAMP' NOT NULL,
  search_pattern VARCHAR(255),
  target_table   VARCHAR(255)                          NOT NULL,
  result_count   INT(11) DEFAULT NULL
)
  ENGINE = InnoDB;

CREATE TABLE search_result (
  search_id   INT(11) UNSIGNED NOT NULL,
  primary_key INT(11)          NOT NULL,
  PRIMARY KEY (search_id, primary_key),
  CONSTRAINT fk_search_search_id1 FOREIGN KEY (search_id) REFERENCES search (search_id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
  ENGINE = InnoDB;
```
