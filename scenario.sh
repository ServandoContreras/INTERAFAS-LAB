#!/usr/bin/env bash
set -euo pipefail
cmd=${1:-status}
case "$cmd" in
  status)
    docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas -e "SELECT phase,phase_name,phase_started_at,last_event_code FROM scenario_state WHERE id=1; SELECT event_code,created_at FROM scenario_events ORDER BY id DESC LIMIT 10;";;
  reset)
    docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas -e "DELETE FROM scenario_events; UPDATE scenario_state SET phase=0,phase_name='Operación normal',phase_started_at=NOW(),last_event_code=NULL WHERE id=1;";;
  flag)
    n=${2:?Uso: ./scenario.sh flag 16}; code=$(printf 'FLAG_%02d' "$n"); docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas -e "INSERT IGNORE INTO scenario_events(event_code,flag_number,source,detail) VALUES('$code',$n,'instructor','Vista previa / prueba');";;
  recover)
    docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas -e "INSERT IGNORE INTO scenario_events(event_code,source,detail) VALUES('RECOVERY_STARTED','instructor','Inicio de recuperación');";;
  *) echo "Uso: $0 {status|reset|flag N|recover}"; exit 1;;
esac
