CREATE TABLE del_rates AS SELECT id FROM mybb_ratespostrating A LEFT JOIN mybb_users B ON A.uid = B.uid WHERE usergroup != 9 AND additionalgroups != '%9%';
CREATE TABLE del_thumbs AS SELECT id FROM mybb_thumbspostrating A LEFT JOIN mybb_users B ON A.uid = B.uid WHERE usergroup != 9 AND additionalgroups != '%9%';
DELETE FROM mybb_ratespostrating WHERE id IN del_rates;
DELETE FROM mybb_thumbspostrating WHERE id IN del_thumbs;
DROP TABLE del_rates;
DROP TABLE del_thumbs;

CREATE TABLE rates AS SELECT pid,SUM(ratesup) AS u,SUM(ratesdown) AS d FROM mybb_ratespostrating GROUP BY pid;
CREATE TABLE thumbs AS SELECT pid,SUM(thumbsup) AS u,SUM(thumbsdown) AS d FROM mybb_thumbspostrating GROUP BY pid;
UPDATE mybb_posts D SET ratesup = COALESCE(u,0),ratesdown = COALESCE(d,0) FROM rates C WHERE C.pid = D.pid;
UPDATE mybb_posts D SET thumbsup = COALESCE(u,0),thumbsdown = COALESCE(d,0) FROM thumbs C WHERE C.pid = D.pid;
DROP TABLE rates;
DROP TABLE thumbs;

CREATE TABLE rates AS SELECT uid,SUM(ratesup) AS u,SUM(ratesdown) AS d FROM mybb_posts GROUP BY uid;
CREATE TABLE thumbs AS SELECT uid,SUM(thumbsup) AS u,SUM(thumbsdown) AS d FROM mybb_posts GROUP BY uid;
UPDATE mybb_users D SET rates_up = COALESCE(u,0),rates_down = COALESCE(d,0) FROM rates C WHERE C.uid = D.uid;
UPDATE mybb_users D SET thumbs_up = COALESCE(u,0),thumbs_down = COALESCE(d,0) FROM thumbs C WHERE C.uid = D.uid;
DROP TABLE rates;
DROP TABLE thumbs;
