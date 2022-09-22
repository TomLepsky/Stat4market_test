<?php

/* ----- Задание 1 ----- 

# ----- №1 ----- 

SELECT u1.* 
FROM `users` u1 
WHERE u1.posts_qty > (
    SELECT u2.posts_qty 
    FROM `users` u2 
    WHERE u2.id = u1.invited_by_user_id
);

# ----- №2 ----- 

SELECT u1.* 
FROM `users` u1 
WHERE u1.posts_qty = (
    SELECT MAX(u2.posts_qty) 
    FROM `users` u2 
    WHERE u1.group_id = u2.group_id
)

# ----- №3 ----- 

SELECT g.*, COUNT(u.id) as users_qty 
FROM `groups` g 
INNER JOIN `users` u ON g.id = u.group_id 
GROUP BY u.group_id 
HAVING users_qty > 10000

# ----- №4 ----- 

SELECT u1.* 
FROM `users` u1 
WHERE u1.group_id <> (
    SELECT u2.group_id 
    FROM `users` u2 
    WHERE u2.id = u1.invited_by_user_id
)

# ----- №5 ----- 

WITH cte_posts_sum AS (
    SELECT g.*, SUM(u.posts_qty) as posts_sum 
 	FROM `groups` g INNER JOIN users u ON g.id = u.group_id 
    GROUP BY u.group_id
)

SELECT * 
FROM `cte_posts_sum`
WHERE posts_sum = (
	SELECT MAX(posts_sum) 
	FROM `cte_posts_sum`
)

/* ----- Задание 2 -----

# Полагаем старая таблица выглядит так

CREATE TABLE old_table_name (
	id int PRIMARY KEY AUTO_INCREMENT,
    old_field_1 int,
    old_field_2 int
);

# Создаём новую структуру таблицы на основе старой

CREATE TABLE new_table_name (
	id int PRIMARY KEY AUTO_INCREMENT,
    old_field_1 int,
    old_field_renamed int,
    new_field_1 int,
    new_field_2 int,
    new_field_3 int,
    INDEX index_1 (new_field_1),
    INDEX index_2 (new_field_2),
    INDEX index_3 (new_field_3)
);

# Создаем временную таблицу для фиксации изменений в старой таблице, ока будем делать дамп

CREATE TABLE tmp (
  id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `row_id` int,
  `action` enum('inserted', 'updated','deleted') NOT NULL
)

# навешиваем триггеры на старую таблицу

CREATE TRIGGER after_insert AFTER INSERT ON old_table_name FOR EACH ROW
BEGIN
  INSERT INTO tmp VALUES (NEW.id, 'inserted');
END

CREATE TRIGGER after_update AFTER UPDATE ON old_table_name FOR EACH ROW
BEGIN
  INSERT INTO tmp VALUES ( NEW.id, 'updated');
END

CREATE TRIGGER after_delete AFTER DELETE ON old_table_name FOR EACH ROW
BEGIN
  INSERT INTO tmp VALUES (OLD.id, 'deleted');
END

# делаем дамп старой таблицы old_table_name, 
# после импортируем эти данные в новую таблицу new_table_name
# далее меняем названия таблиц местами
RENAME TABLE old_table_name TO _tmp
RENAME TABLE new_table_name TO old_table_name

#обновляем новую измененную таблицу данными из tmp

CREATE PROCEDURE update_new_table()
BEGIN
	DECLARE row_id int;
	DECLARE action varchar;
	DECLARE old_field_1 int;
	DECLARE old_field_2 int;
	DECLARE cur CURSOR for SELECT row_id, action FROM tmp;
	DECLARE continue handler for not found set done = true;

	OPEN cur;
    start_loop: LOOP
        FETCH cur INTO row_id, action;
        if done then
            leave start_loop;
        end if;
        CASE action
        	WHEN 'inserted' THEN
        		INSERT INTO old_table_name (
        			SELECT old_field_1, old_field_2 FROM new_table_name WHERE id = row_id
    			);

        	WHEN 'updated' THEN
        		SELECT old_field_1,old_field_2 INTO @old_field_1, @old_field_2 
        		FROM new_table_name 
        		WHERE id = row_id;
        		UPDATE old_table_name SET old_field_1 = @old_field_1, old_field_renamed = @old_field_2 
        		WHERE id - row_id;

        	WHEN 'deleted' THEN
        		DELETE FROM old_table_name 
        		WHERE id = row_id;
        	
    END LOOP;
    CLOSE cur;
END

CALL update_new_table();

*/


/* ----- Задание 3 ----- */



function tuesdayQty(DateTime $from, DateTime $to) : int
{
	$daysBetween = $from->diff($to)->days;
	$qty = (int)($daysBetween / 7);
	$weekDayFrom = (int)$from->format('w');
	$weekDayTo = (int)$to->format('w');
	if (($weekDayFrom <= 2 && $weekDayTo > 2)) {
		$qty++;
	}
	return $qty;
}

echo tuesdayQty(new DateTime('29-08-2022'), new DateTime('26-09-2022'));
