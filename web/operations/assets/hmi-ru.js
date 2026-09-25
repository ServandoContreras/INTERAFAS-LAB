(()=>{'use strict';
const RU={
'METROPOLITAN WATER OPERATIONS':'АСУ ТП · METROPOLITAN WATER OPERATIONS',
'OPERATIONS':'ОПЕРАЦИИ / OPERATIONS','PROCESS':'ПРОЦЕСС / PROCESS','SYSTEM':'СИСТЕМА / SYSTEM','ENGINEERING':'ИНЖИНИРИНГ / ENGINEERING','ANALYSIS':'АНАЛИЗ / ANALYSIS',
'Overview':'ОБЗОР / Overview','Stations':'СТАНЦИИ / Stations','Metropolitan Process':'МЕТРОСИСТЕМА / Metropolitan','Process View':'ТЕХСХЕМА / Process View',
'Trends':'ТРЕНДЫ / Trends','Alarms':'АВАРИИ / Alarms','Assets':'ОБЪЕКТЫ / Assets','Network':'СЕТЬ / Network','Automation':'АВТОМАТИКА / Automation',
'Maintenance':'ТО / Maintenance','Configuration':'КОНФИГУРАЦИЯ / Configuration','Firmware':'ПРОШИВКА / Firmware','Events':'СОБЫТИЯ / Events','Statistics':'СТАТИСТИКА / Statistics',
'MODE AUTO':'РЕЖИМ · АВТО','Total Supply':'ПОДАЧА / Total Supply','Total Demand':'ПОТРЕБЛЕНИЕ / Total Demand','System Reserve':'РЕЗЕРВ / System Reserve',
'Average Pressure':'ДАВЛЕНИЕ / Average','Water Quality':'КАЧЕСТВО ВОДЫ','Active Alarms':'АВАРИИ / Active','Availability':'ГОТОВНОСТЬ',
'PLC Online':'PLC · СВЯЗЬ','RTU Online':'RTU · СВЯЗЬ','OT Services':'СЕРВИСЫ OT','Unack Alarms':'НЕПОДТВ. АВАРИИ',
'PROCESS OVERVIEW':'ОБЗОР ПРОЦЕССА','OPEN PROCESS':'ОТКРЫТЬ ПРОЦЕСС','OPEN PROCESS VIEW':'ОТКРЫТЬ ТЕХСХЕМУ',
'Function':'ФУНКЦИЯ','Controller':'КОНТРОЛЛЕР','Gateway':'ШЛЮЗ','Supply':'ПОДАЧА','Demand':'ПОТРЕБЛЕНИЕ','Reserve':'РЕЗЕРВ','Pressure':'ДАВЛЕНИЕ',
'Population':'НАСЕЛЕНИЕ','Service zones':'ЗОНЫ','LIVE':'В РЕАЛЬНОМ ВРЕМЕНИ','AUTO':'АВТО','FLOW':'РАСХОД','HEADER PRESSURE':'ДАВЛЕНИЕ КОЛЛЕКТОРА',
'RESERVOIR':'УРОВЕНЬ','CHLORINE':'ХЛОР','TURBIDITY':'МУТНОСТЬ','RTU LATENCY':'ЗАДЕРЖКА RTU','MODE':'РЕЖИМ','LIVE PROCESS':'ТЕХСХЕМА',
'EQUIPMENT':'ОБОРУДОВАНИЕ','ALARMS':'АВАРИИ','MAINTENANCE':'ТЕХОБСЛУЖИВАНИЕ','NORMAL':'НОРМА','WARNING':'ПРЕДУПРЕЖДЕНИЕ','ALARM':'АВАРИЯ',
'OFFLINE / STOP':'НЕТ СВЯЗИ / СТОП','Metropolitan Alarm Management':'УПРАВЛЕНИЕ АВАРИЯМИ','Alarm & Event Queue':'ОЧЕРЕДЬ АВАРИЙ И СОБЫТИЙ',
'Time':'ВРЕМЯ','Priority':'ПРИОРИТЕТ','Station':'СТАНЦИЯ','Source':'ИСТОЧНИК','Condition':'СОСТОЯНИЕ','State':'СТАТУС',
'Metropolitan OT Asset Inventory':'ИНВЕНТАРЬ ОБЪЕКТОВ OT','Observed Assets':'НАБЛЮДАЕМЫЕ ОБЪЕКТЫ','Asset':'ОБЪЕКТ','Type':'ТИП','Zone':'ЗОНА','Status':'СТАТУС',
'Zones & Conduits':'ЗОНЫ И КАНАЛЫ СВЯЗИ','Operational Architecture':'АРХИТЕКТУРА АСУ ТП','Automation Jobs':'ЗАДАНИЯ АВТОМАТИКИ','Automation Engine':'ДВИЖОК АВТОМАТИКИ',
'Engineering Context':'ИНЖЕНЕРНЫЙ КОНТЕКСТ','Control Projects':'ПРОЕКТЫ УПРАВЛЕНИЯ','Operational Event Timeline':'ЖУРНАЛ ОПЕРАЦИОННЫХ СОБЫТИЙ',
'Live Operational Stream':'ПОТОК СОБЫТИЙ','Timeline':'ХРОНОЛОГИЯ','Severity':'КРИТИЧНОСТЬ','Message':'СООБЩЕНИЕ',
'Metropolitan Operational Statistics':'ОПЕРАЦИОННАЯ СТАТИСТИКА','Alarm Distribution':'РАСПРЕДЕЛЕНИЕ АВАРИЙ','Event Severity':'КРИТИЧНОСТЬ СОБЫТИЙ'
};
function one(n){const r=n.nodeValue,k=r.trim();if(!k||!(k in RU))return;const a=r.match(/^\s*/)?.[0]||'',b=r.match(/\s*$/)?.[0]||'';n.nodeValue=a+RU[k]+b}
function scan(root){if(!root)return;const w=document.createTreeWalker(root,NodeFilter.SHOW_TEXT),a=[];while(w.nextNode())a.push(w.currentNode);a.forEach(one)}
function init(){scan(document.body);new MutationObserver(ms=>{for(const m of ms)for(const n of m.addedNodes)n.nodeType===3?one(n):n.nodeType===1&&scan(n)}).observe(document.body,{childList:true,subtree:true})}
document.readyState==='loading'?document.addEventListener('DOMContentLoaded',init,{once:true}):init();
})();