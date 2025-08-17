function validateLogin() {
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  if (username === "" || password === "") {
    alert("Tafadhali jaza jina la mtumiaji na nenosiri.");
    return false;
  }

  // Example verification logic
  if (username === "hamisi" && password === "love2025") {
    alert("Umefanikiwa kuingia!");
    return true;
  } else {
    alert("Jina la mtumiaji au nenosiri si sahihi.");
    return false;
  }
}