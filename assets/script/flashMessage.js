console.log('flashMessage hhhh')




document.addEventListener("turbo:load", () => {
  setTimeout(() => {
    document.querySelectorAll(".flash-message").forEach((el) => {
      el.classList.remove("show");
      el.classList.add("fade");
      setTimeout(() => el.remove(), 300); // suppression DOM
    });
  }, 3000); // 3 secondes
});