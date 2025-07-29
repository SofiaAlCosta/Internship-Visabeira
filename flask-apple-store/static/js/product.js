$(document).ready(function () {
  console.log("✔️ product.js carregado");

  let slideIndex = 1;
  showSlides(slideIndex);

  $(".armazenamento button").click(function () {
    $(".armazenamento button.ativo").removeClass("ativo");
    $(this).addClass("ativo");
    $("#productprice").html($(this).data("price"));
  });

  $(".cores button").click(function () {
    $(".cores button.active").removeClass("active");
    $(this).addClass("active");
    const slideNum = $(this).data("slide");
    currentSlide(slideNum);
  });

  const slideImages = document.querySelectorAll(".mySlide img");
  let currentModalIndex = 0;

  slideImages.forEach((img, index) => {
    img.style.cursor = "pointer";
    img.addEventListener("click", function () {
      currentModalIndex = index;
      openModal(currentModalIndex);
    });
  });

  const modal = document.getElementById("myModal");
  const modalImg = document.getElementById("img01");
  const captionText = document.getElementById("caption");
  const modalPrev = document.querySelector(".modal-prev");
  const modalNext = document.querySelector(".modal-next");
  const closeBtn = document.getElementsByClassName("close")[0];

  function openModal(index) {
    if (!modal || !modalImg || !captionText) return;
    modal.style.display = "block";
    modalImg.src = slideImages[index].src;
    captionText.innerHTML = slideImages[index].alt || "";
  }

  if (modalPrev) {
    modalPrev.onclick = function () {
      currentModalIndex = (currentModalIndex - 1 + slideImages.length) % slideImages.length;
      openModal(currentModalIndex);
    };
  }

  if (modalNext) {
    modalNext.onclick = function () {
      currentModalIndex = (currentModalIndex + 1) % slideImages.length;
      openModal(currentModalIndex);
    };
  }

  if (closeBtn) {
    closeBtn.onclick = function () {
      modal.style.display = "none";
    };
  }

  const videoModal = document.getElementById("videoModal");
  const openVideoBtn = document.getElementById("openVideoModal");
  const closeVideoBtn = document.querySelector(".video-close");

  if (openVideoBtn && videoModal) {
    openVideoBtn.onclick = function () {
      videoModal.style.display = "block";
    };
  }

  if (closeVideoBtn && videoModal) {
    closeVideoBtn.onclick = function () {
      videoModal.style.display = "none";
      const iframe = videoModal.querySelector("iframe");
      if (iframe) iframe.src = iframe.src;
    };
  }

  window.onclick = function (event) {
    if (event.target === videoModal) {
      videoModal.style.display = "none";
      const iframe = videoModal.querySelector("iframe");
      if (iframe) iframe.src = iframe.src;
    }
  };

  function plusSlides(n) {
    showSlides(slideIndex += n);
  }

  function currentSlide(n) {
    showSlides(slideIndex = n);
  }

  function showSlides(n) {
    let i;
    const slides = document.getElementsByClassName("mySlide");
    const dots = document.getElementsByClassName("dot");

    if (slides.length === 0) return;

    if (n > slides.length) { slideIndex = 1 }
    if (n < 1) { slideIndex = slides.length }

    for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
    }

    for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" ativa", "");
    }

    if (slides[slideIndex - 1]) {
      slides[slideIndex - 1].style.display = "block";
    }
    if (dots[slideIndex - 1]) {
      dots[slideIndex - 1].className += " ativa";
    }
  }

  window.plusSlides = plusSlides;
  window.currentSlide = currentSlide;
});