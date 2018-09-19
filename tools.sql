select if(p.home_away = 'a', s.away, s.home) as teampick,
  y.playername
  from nspx_leagueplayer as y,
  users as u,
  schedules as s,
  picks as p,
  league as g
  where y.leagueid = g.league_id
    and u.username = p.user
    and p.schedule_id = s.schedule_id
    and u.id = y.userid
    and g.league_id = p.league_id
    and g.league_id = 147
    and s.week = 4;
  