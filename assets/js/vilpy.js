window.onload = () => {
  /*===============*/
  /*= Globaal.    =*/
  /*===============*/
  const getSelected = (selector) => {
    const checks = document.querySelectorAll(selector);
    const checked = [];
    checks.forEach((item) => {
      if (item.checked) {
        checked.push(item);
      }
    });
    return checked.length > 0 ? checked : null;
  };

  //Function to set parameter in url
  function setQueryStringParameter(name, value) {
    const params = new URLSearchParams(window.location.search);
    params.set(name, value);
    window.history.replaceState(
      {},
      "",
      decodeURIComponent(`${window.location.pathname}?${params}`)
    );
  }

  //Select plugin tab if coming from param
  const url = window.location.search;
  const params = new URLSearchParams(url);
  const tab = params.get("tab");
  const selectTab = (tabname) => {
    tabname = tabname ?? "default";
    const name = tabname;
    const tabcontent = document.getElementById(name);

    document.querySelectorAll(".active-button").forEach((el) => {
      el.classList.remove("active-button");
    });
    document.querySelectorAll(".active-tab").forEach((el) => {
      el.classList.remove("active-tab");
    });

    const selectedTab = document.querySelector(`.nav-tab[data-tab=${tabname}]`);
    if (selectedTab) {
      selectedTab.classList.add("active-button");
      tabcontent.classList.add("active-tab");
    } else {
      //If tab is non existant, do the default one
      document
        .querySelector(`.nav-tab[data-tab=default]`)
        .classList.add("active-button");
      document.getElementById("default").classList.add("active-tab");
    }
  };
  selectTab(tab);

  const tabs = document.querySelectorAll(".nav-tab");
  tabs.forEach((item, index) => {
    const name = document.querySelectorAll(".nav-tab")[0].dataset.tab;
    const tabcontent = document.getElementById(name);

    //If tab is specified in url dont open first tab automatically
    if (!tab) {
      if (index === 0) {
        item.classList.add("active-button");
        tabcontent.classList.add("active-tab");
      }
    }

    item.addEventListener("click", function () {
      const name = this.dataset.tab;
      const selectedID = this.getAttribute("data-tab");
      setQueryStringParameter("tab", selectedID);

      //Set id in url
      let page = new URL(window.location.href);
      page.searchParams.append("tab", selectedID);

      const tabcontent = document.getElementById(name);

      document.querySelectorAll(".active-button").forEach((el) => {
        el.classList.remove("active-button");
      });
      document.querySelectorAll(".active-tab").forEach((el) => {
        el.classList.remove("active-tab");
      });

      this.classList.add("active-button");

      tabcontent.classList.add("active-tab");
    });
  });

  /*===============*/
  /*= Algemeen.   =*/
  /*===============*/

  const logoUploadButton = document.getElementById("logoUploadButton");
  const logoDeleteButton = document.getElementById("logoDeleteButton");
  const backgroundUploadButton = document.getElementById("bgUploadButton");
  const backgroundDeleteButton = document.getElementById("bgDeleteButton");

  let logoLibrary = window.wp.media({
    title: "'Selecteer logo',",
    multiple: false,
    library: {
      order: "DESC",
      orderby: "date",
      type: "image",
      search: null,
      uploadedTo: null,
    },
    button: {
      text: "Upload",
    },
  });

  let bgLibrary = window.wp.media({
    title: "'Selecteer achtergrond',",
    multiple: false,
    library: {
      order: "DESC",
      orderby: "date",
      type: "image",
      search: null,
      uploadedTo: null,
    },
    button: {
      text: "Upload",
    },
  });

  logoUploadButton.addEventListener("click", (e) => {
    e.preventDefault();
    if (logoLibrary) {
      logoLibrary.open();
      return;
    }
  });

  logoLibrary.on("select", function () {
    const selectedImages = logoLibrary.state().get("selection").first();
    document.getElementById("vilpyLogoMedia").value =
      selectedImages.attributes.url;
    document.getElementById("imgLogo").src = selectedImages.attributes.url;
  });

  document.getElementById("imgLogo").style.width =
    document.getElementById("clientLogoSize").value + "px";
  document
    .getElementById("clientLogoSize")
    .addEventListener("input", function () {
      document
        .getElementById("clientLogoSize")
        .setAttribute("value", this.value);
      document.getElementById("imgLogo").style.width = this.value + "px";
    });

  logoDeleteButton.addEventListener("click", (e) => {
    e.preventDefault();
    document.getElementById("vilpyLogoMedia").value = "";
    const defaultLogo = document.getElementById("defaultLogoUrl").value;
    document.getElementById("imgLogo").src = defaultLogo;
  });

  backgroundUploadButton.addEventListener("click", (e) => {
    e.preventDefault();
    if (bgLibrary) {
      bgLibrary.open();
      return;
    }
  });

  bgLibrary.on("select", function () {
    const selectedImages = bgLibrary.state().get("selection").first();
    document.getElementById("vilpyBgMedia").value =
      selectedImages.attributes.url;
    document.getElementById("imgBg").src = selectedImages.attributes.url;
  });

  backgroundDeleteButton.addEventListener("click", (e) => {
    e.preventDefault();
    document.getElementById("vilpyBgMedia").value = "";
    document.getElementById("imgBg").src =
      "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
  });

  //Color picker
  jQuery("#accentField").wpColorPicker();
  jQuery("#overlayField").wpColorPicker();
  jQuery("#titleColorField").wpColorPicker();

  //Popup
  const body = document.body;
  const popup = document.createElement("div");
  popup.setAttribute("id", "basbox");
  popup.innerHTML = ` 
    <div class="popup">
        <h2>De anti-Bas popup</h2>
        <p> Met deze optie kun je <b>ALLES PERMANENT</b> verwijderen</p>
        <a id="continue-button" class="button button-secondary">Ik weet wat ik doe!</a> 
        <a id="stop-button" onclick="body.removeChild(document.getElementById('basbox')); " class="button button-primary">Oeps laat maar!</a>
    </div>`;
  popup.setAttribute("class", "antibas");

  //create testing env
  document
    .getElementById("setupTestEnvironment")
    .addEventListener("click", function () {
      body.appendChild(popup);

      document
        .querySelector("#continue-button")
        .addEventListener("click", function confirmAutoEnv() {
          document
            .querySelector("#continue-button")
            .removeEventListener("click", confirmAutoEnv);
          body.removeChild(popup);
          runAutoEnv();
        });
    });

  const setupTestEnv = document.getElementById("setupTestEnvironment");
  const runAutoEnv = () => {
    const loader = document.createElement("span");
    loader.setAttribute("class", "spinner is-active");
    loader.style.float = "none";
    setupTestEnv.parentNode.insertBefore(loader, setupTestEnv.nextSibling);
    const succesText = document.createElement("span");
    const successTextDom = document.querySelector(".hh-successtext");
    if (successTextDom) {
      successTextDom.remove();
    }
    const shoporsite = getSelected(".shoporsite");
    let selectedRadios = [];
    shoporsite.forEach((item) => {
      selectedRadios.push(item.value);
    });
    jQuery.post(
      admin_url.ajax_url,
      {
        action: "testenvironmentsetup",
        shoporweb: selectedRadios[0],
      },
      function (res) {
        document.querySelector(".spinner").remove();
        succesText.style.color = "green";
        succesText.setAttribute("class", "hh-successtext");
        succesText.style.verticalAlign = "sub";
        succesText.style.paddingLeft = "10px";
        succesText.innerText = "Testomgeving opgezet!";
        setupTestEnv.parentNode.insertBefore(
          succesText,
          setupTestEnv.nextSibling
        );
      }
    );
  };

  /*========================*/
  /*= Plugins installeren. =*/
  /*========================*/

  const installPlugins = document.getElementById("installPlugins");
  const activateInstalledPlugins = document.getElementById(
    "activateInstalledPlugins"
  );
  if (installPlugins) {
    installPlugins.addEventListener("click", () => {
      //Create array with slugs from selected checkboxes
      const selectedChecks = getSelected(".plugin-checkbox");
      const checks = [];
      selectedChecks.forEach((item) => {
        checks.push(item.value);
      });

      //This is a recursive function that calls itsself. This is needed to do the Ajax calls one at a time
      if (checks !== null) {
        let initial = 0;
        const nextCall = (i) => {
          const item = checks[i];
          const loader = document.querySelector("#" + checks[i]).parentElement
            .nextElementSibling;
          loader.style.display = "inline";
          jQuery.post(
            admin_url.ajax_url,
            {
              action: "installplugins",
              plugins: checks[i],
            },
            function (res) {
              initial++;
              loader.style.display = "none";
              let statusIcon = document.createElement("span");
              let succesOrNot = document.querySelector("." + item);
              if (res === "success") {
                statusIcon.className = "dashicons dashicons-yes-alt";
              } else {
                statusIcon.className = "dashicons dashicons-dismiss";
              }
              succesOrNot.parentNode.insertBefore(
                statusIcon,
                succesOrNot.nextSibling
              );
              if (initial == checks.length) {
                setTimeout(() => {
                  location.reload();
                }, 1000);
              } else {
                nextCall(initial);
              }
            }
          );
        };
        nextCall(initial);
        console.log("Vilpy: started plugin installation");
      } else {
        console.log("Vilpy: no plugins selected");
      }
    });

    const installedList = document.querySelector(".plugin-installed");

    if (!installedList) {
      document.getElementById("activateInstalledPlugins").style.opacity = 0.5;
      document.getElementById("activateInstalledPlugins").style.pointerEvents =
        "none";
    }

    activateInstalledPlugins.addEventListener("click", () => {
      if (installedList) {
        document.querySelector(".smallloader-activate").style.display =
          "inline";
        jQuery.post(
          admin_url.ajax_url,
          {
            action: "activateplugins",
          },
          function (res) {
            document.querySelector(".smallloader-activate").style.display =
              "none";
            const text = document.createElement("div");
            text.textContent = "Alle plugins geactiveerd";
            const child = document.getElementById("activateInstalledPlugins");
            text.style.marginTop = "10px";
            child.parentNode.appendChild(text, child);
            setTimeout(() => {
              location.reload();
            }, 500);
          }
        );
        console.log("Vilpy: started plugin activation");
      } else {
        console.log("Vilpy: no plugins installed");
      }
    });

    const selectAll = document.querySelector(".select-all");
    if (installedList) {
      selectAll.style.opacity = 0.5;
      selectAll.style.pointerEvents = "none";
    }

    selectAll.addEventListener("click", (e) => {
      e.preventDefault();
      const checkboxes = document.querySelectorAll(".plugin-checkbox");
      checkboxes.forEach((item) => {
        item.checked = true;
      });
    });

    /*===================*/
    /*= Thema installeren. =*/
    /*===================*/

    //Create array with slugs from selected checkboxes
    document
      .getElementById("installThemes")
      .addEventListener("click", function () {
        const selectedThemes = getSelected(".theme-checkbox");
        if (!selectedThemes) {
          return;
        }
        const themeChecks = [];
        selectedThemes.forEach((item) => {
          themeChecks.push(item.value);
        });

        //This is a recursive function that calls itsself. This is needed to do the Ajax calls one at a time
        if (themeChecks !== null) {
          let initial = 0;
          const nextCall = (i) => {
            const item = themeChecks[i];
            console.log(item);
            const loader = document.querySelector("#" + themeChecks[i])
              .parentElement.nextElementSibling;
            loader.style.display = "inline";
            jQuery.post(
              admin_url.ajax_url,
              {
                action: "installthemes",
                theme: themeChecks[i],
              },
              function (res) {
                initial++;
                loader.style.display = "none";
                const output = document.getElementById("output");
                output.innerHTML = res;
                if (initial == themeChecks.length) {
                  //location.reload();
                } else {
                  nextCall(initial);
                }
              }
            );
          };
          nextCall(initial);
          console.log("Vilpy: started theme installation");
        } else {
          console.log("Vilpy: no themes selected");
        }
      });

    /*===================*/
    /*= Vilpy klant instellingen. =*/
    /*===================*/
  }

  /*===================*/
  /*= Extra. =*/
  /*===================*/
  
  const showcaseModeOption = document.querySelector("#enable-showcase-mode");

  if (showcaseModeOption) {
      if (!showcaseModeOption.checked) {
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(3)"
        ).style.display = "none";
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(4)"
        ).style.display = "none";
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(5)"
        ).style.display = "none";
      }
    showcaseModeOption.addEventListener('click', () => {
      if (!showcaseModeOption.checked) {
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(3)"
        ).style.display = "none";
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(4)"
        ).style.display = "none";
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(5)"
        ).style.display = "none";
      } else {
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(3)"
        ).style.display = "table-row";
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(4)"
        ).style.display = "table-row";
        document.querySelector(
          "#extra > form > table > tbody > tr:nth-child(5)"
        ).style.display = "table-row";
      }
    })
  }

}
