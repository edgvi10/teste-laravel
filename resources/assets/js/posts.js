
document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    var users = [];
    var posts = [];

    var fields = {
      id: { label: "ID", type: "input", readonly: true, required: true, placeholder: "autoincrement" },
      user_id: { label: "User ID", type: "input", readonly: true, required: true, placeholder: "autoincrement" },
      title: { label: "Title", size: "col-md-8", type: "input", required: false, placeholder: "Title" },
      body: { label: "Body", size: "col-md-12", type: "textarea", required: false, placeholder: "Body" },
    }

    const users_select = document.querySelector(".users_select");
    const refresh_button = document.querySelector("#load-posts");
    const form = document.querySelector("#post_form");

    var user_id = "";

    const base_url = "https://gorest.co.in/public/v2";

    for (const key in fields) {
      const field = fields[key];
      const div = document.createElement("div");
      div.classList.add(field.size ?? "col-md-4");
      const label = document.createElement("label");
      label.textContent = field.label;
      label.setAttribute("for", key);
      div.appendChild(label);
      const input = document.createElement(field.type);
      input.classList.add("form-control");
      input.id = key;
      input.name = key;
      input.placeholder = field.placeholder;
      input.value = field.value ?? "";

      if (field.type === "textarea") {
        input.setAttribute("rows", 3);
      }

      if (field.readonly) {
        input.setAttribute("readonly", true);
      }
      if (field.required) {
        input.setAttribute("required", true);
      }

      div.appendChild(input);
      form.appendChild(div);
    }

    async function getUsers(filter, order) {
      try {
        const request = await fetch(`${base_url}/users`);
        const response = await request.json();

        for (const user of response) {
          const option = document.createElement("option");
          option.value = user.id;
          option.textContent = user.name;
          users_select.appendChild(option);
        }
      } catch (error) {
        console.log(error);
      }
    }

    getUsers();
    users_select.addEventListener("change", function (event) {
      getPosts({ user_id: event.target.value });
    });

    if (refresh_button) {
      refresh_button.addEventListener("click", getPosts);
    }

    const carregando = document.querySelector(".carregando");
    async function getPosts(filter = {}, order) {
      try {

        carregando.classList.remove("d-none");
        console.log("Here we go");
        const url = (filter.user_id) ? `${base_url}/users/${filter.user_id}/posts` : `${base_url}/posts`;
        const request = await fetch(url);
        const response = await request.json();

        const thead = document.querySelector("thead tr");
        const tbody = document.querySelector("tbody");

        console.log(thead, tbody)
        thead.innerHTML = "";
        tbody.innerHTML = "";

        if (response.length === 0) {
          const tr = document.createElement("tr");
          const td = document.createElement("td");
          td.textContent = "Nenhum post encontrado";
          tr.appendChild(td);
          tbody.appendChild(tr);
        } else {

          const json_header = Object.keys(response[0]);
          for (const key of json_header) {
            const th = document.createElement("th");
            th.textContent = key;
            thead.appendChild(th);
          }

          for (const row of response) {
            const tr = document.createElement("tr");
            for (const key of json_header) {
              const td = document.createElement("td");
              td.textContent = row[key];
              tr.appendChild(td);
            }
            tbody.appendChild(tr);
          }
        }

      } catch (error) {
        console.log(error);
      } finally {

        carregando.classList.add("d-none");

      }
    }
    getPosts();

  })();
});
