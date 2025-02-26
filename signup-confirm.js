document.addEventListener("DOMContentLoaded", function () {
    try {
        let validityData = JSON.parse(validity);
        let container = document.querySelector(".container");

        let messageElement = document.createElement("p");
        messageElement.style.fontSize = "18px";
        messageElement.style.fontWeight = "bold";
        messageElement.textContent = validityData.message;

        switch (validityData.status) {
            case -1:
                messageElement.style.color = "red";
                container.style.backgroundColor = "rgba(255, 0, 0, 0.1)";
                break;
            case 0:
                messageElement.style.color = "green";
                container.style.backgroundColor = "rgba(0, 255, 0, 0.1)";
                break;
            case 1:
                messageElement.style.color = "blue";
                container.style.backgroundColor = "rgba(0, 0, 255, 0.1)";
                break;
        };

        container.appendChild(messageElement);
    } catch (error) {
        console.error("Invalid JSON data:", error);
    }
});
