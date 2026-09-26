(()=>{'use strict';
const RU={
'METROPOLITAN WATER OPERATIONS':'АСУ ТП · ВОДОСНАБЖЕНИЕ МЕТРОСИСТЕМЫ',
'OPERATIONS':'ОПЕРАЦИИ','PROCESS':'ПРОЦЕСС','SYSTEM':'СИСТЕМА','ENGINEERING':'ИНЖИНИРИНГ','ANALYSIS':'АНАЛИЗ',
'Overview':'ОБЗОР','Stations':'СТАНЦИИ','Metropolitan Process':'МЕТРОСИСТЕМА','Process View':'ТЕХСХЕМА',
'Trends':'ТРЕНДЫ','Alarms':'АВАРИИ','Assets':'ОБЪЕКТЫ','Network':'СЕТЬ','Automation':'АВТОМАТИКА',
'Maintenance':'ТЕХОБСЛУЖИВАНИЕ','Configuration':'КОНФИГУРАЦИЯ','Firmware':'ПРОШИВКА','Events':'СОБЫТИЯ','Statistics':'СТАТИСТИКА',
'MODE AUTO':'РЕЖИМ · АВТО','Total Supply':'ПОДАЧА','Total Demand':'ПОТРЕБЛЕНИЕ','System Reserve':'РЕЗЕРВ',
'Average Pressure':'СРЕДНЕЕ ДАВЛЕНИЕ','Water Quality':'КАЧЕСТВО ВОДЫ','Active Alarms':'АКТИВНЫЕ АВАРИИ','Availability':'ГОТОВНОСТЬ',
'PLC Online':'PLC · СВЯЗЬ','RTU Online':'RTU · СВЯЗЬ','OT Services':'СЕРВИСЫ OT','Unack Alarms':'НЕПОДТВ. АВАРИИ',
'PROCESS OVERVIEW':'ОБЗОР ПРОЦЕССА','OPEN PROCESS':'ОТКРЫТЬ ПРОЦЕСС','OPEN PROCESS VIEW':'ОТКРЫТЬ ТЕХСХЕМУ',
'Function':'ФУНКЦИЯ','Controller':'КОНТРОЛЛЕР','Gateway':'ШЛЮЗ','Supply':'ПОДАЧА','Demand':'ПОТРЕБЛЕНИЕ','Reserve':'РЕЗЕРВ','Pressure':'ДАВЛЕНИЕ',
'Population':'НАСЕЛЕНИЕ','Service zones':'ЗОНЫ','LIVE':'В РЕАЛЬНОМ ВРЕМЕНИ','AUTO':'АВТО','FLOW':'РАСХОД','HEADER PRESSURE':'ДАВЛЕНИЕ КОЛЛЕКТОРА',
'RESERVOIR':'УРОВЕНЬ','CHLORINE':'ХЛОР','TURBIDITY':'МУТНОСТЬ','RTU LATENCY':'ЗАДЕРЖКА RTU','MODE':'РЕЖИМ','LIVE PROCESS':'ТЕХСХЕМА',
'EQUIPMENT':'ОБОРУДОВАНИЕ','ALARMS':'АВАРИИ','MAINTENANCE':'ТЕХОБСЛУЖИВАНИЕ','NORMAL':'НОРМА','WARNING':'ПРЕДУПРЕЖДЕНИЕ','ALARM':'АВАРИЯ',
'OFFLINE / STOP':'НЕТ СВЯЗИ / СТОП','Metropolitan Alarm Management':'УПРАВЛЕНИЕ АВАРИЯМИ','Alarm & Event Queue':'ОЧЕРЕДЬ АВАРИЙ И СОБЫТИЙ',
'Time':'ВРЕМЯ','Priority':'ПРИОРИТЕТ','Station':'СТАНЦИЯ','Source':'ИСТОЧНИК','Condition':'СОСТОЯНИЕ','State':'СТАТУС',
'Metropolitan OT Asset Inventory':'ИНВЕНТАРЬ ОБЪЕКТОВ OT','Observed Assets':'НАБЛЮДАЕМЫЕ ОБЪЕКТЫ','Asset':'ОБЪЕКТ','Type':'ТИП','Zone':'ЗОНА','Status':'СТАТУС','Asset Context':'КОНТЕКСТ ОБЪЕКТА','Selected system':'ВЫБРАННАЯ СИСТЕМА','Dependency class':'КЛАСС ЗАВИСИМОСТИ','Related systems':'СВЯЗАННЫЕ СИСТЕМЫ','Dependency path':'ЦЕПОЧКА ЗАВИСИМОСТЕЙ','Operational dependency model':'МОДЕЛЬ ОПЕРАЦИОННЫХ ЗАВИСИМОСТЕЙ',
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