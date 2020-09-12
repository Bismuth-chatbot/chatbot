const targetNode = document.getElementsByClassName('tchat-content')[0]
const config = { attributes: false, childList: true, subtree: true };
const callback = function(mutationsList, observer) {
    // Use traditional 'for loops' for IE 11
    for(let mutation of mutationsList) {
        if (mutation.type !== 'childList' || mutation.target.tagName !== 'P' || !mutation.addedNodes['0']) {
          continue;
        }

        const content = mutation.addedNodes['0'].textContent;
      fetch('http://localhost:8080/.well-known/mercure', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.GFRUFE2C1GaLTnX2WZnO3SoeOM0rrVcI0yph1K_Oo-w'}, body: `topic=https://livestorm.co/chat&data=${content}`})
    }
};

const observer = new MutationObserver(callback);
observer.observe(targetNode, config);
