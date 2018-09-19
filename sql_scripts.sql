select count(*), u.username, y.playername
from schedules s, 
picks as p,
users as u,
nspx_leagueplayer as y
where s.schedule_id = p.schedule_id
and p.user = u.username
and y.userid = u.id
and y.leagueid = p.league_id
and s.week = 7
and p.league_id = 1
group by u.username