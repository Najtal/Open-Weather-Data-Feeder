


# Get the latest enregistrements de tout les villes 

SELECT *
FROM "weather" w1
WHERE creation_time = (SELECT MAX(creation_time) FROM `weather` w2 WHERE w1.id = w2.id)
GROUP BY id;


# Get DB size in Mb

SELECT table_schema "xxxxxxxxxxxx",
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) "DB Size in MB" 
FROM information_schema.tables 
GROUP BY table_schema; 



# Get DATA in Louvain-la-Neuve
# Plot them with PhpMyAdmin

SELECT * FROM `weather` WHERE id = 2792073 ORDER BY `creation_time` ASC