// charts.js - helper to initialize AOS/Chart.js placeholders
export function initAOS(){
    if (window.AOS) AOS.init({duration:600,easing:'ease-out-cubic',once:true});
}

export function createLineChart(ctx, data, options={}){
    if (!window.Chart) return null;
    return new Chart(ctx, {type:'line',data,options});
}

export function createPieChart(ctx, data, options={}){
    if (!window.Chart) return null;
    return new Chart(ctx, {type:'pie',data,options});
}
