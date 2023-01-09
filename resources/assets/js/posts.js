document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
        var users = [];
        var posts = [];

        var fields = {
            id: { label: "ID", type: "input", readonly: true, required: true, placeholder: "autoincrement" },
            user_id: { label: "User ID", type: "select", size: "col-md-6", required: true, placeholder: "Autoincrement", options: [] },
            title: { label: "Title", size: "col-md-8", type: "input", required: false, placeholder: "Title" },
            body: { label: "Body", size: "col-md-12", type: "textarea", required: false, placeholder: "Body" },
        }

        const form_field_values = {};

        const users_select = document.querySelector(".users_select");
        const refresh_button = document.querySelector("#load-posts");
        const form = document.querySelector("#post_form");
        const modal_html = document.querySelector(".post-form-modal");
        const modal = new bootstrap.Modal(modal_html);

        modal_html.addEventListener('hidden.bs.modal', function (event) {
            form_field_values.id = "";
            form_field_values.user_id = "";
            form_field_values.title = "";
            form_field_values.body = "";
            FormBuilder();
        });

        var user_id = "";

        const base_url = "https://gorest.co.in/public/v2";

        function FormBuilder() {
            form.innerHTML = "";

            for (const key in fields) {
                const field = fields[key];
                const div = document.createElement("div");
                div.classList.add(field.size ?? "col-md-4");
                if (field.label) {
                    const label = document.createElement("label");
                    label.textContent = field.label;
                    label.setAttribute("for", key);
                    div.appendChild(label);
                }
                const input = document.createElement(field.type);
                input.classList.add(field.type === "select" ? "form-select" : "form-control");

                input.id = key;
                input.name = key;
                input.placeholder = field.placeholder;

                if (form_field_values[key]) {
                    input.value = form_field_values[key];
                }

                if (field.options) {

                    for (const option of field.options) {
                        const option_html = document.createElement("option");
                        option_html.value = option.value;
                        option_html.textContent = option.label;
                        if (form_field_values[key] === option.value) option_html.setAttribute("selected", true);
                        input.appendChild(option_html);
                    }
                }

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
        }

        FormBuilder();

        async function getUsers(filter, order) {
            try {
                const request = await fetch(`${base_url}/users`);
                const response = await request.json();

                let users = [];
                for (const user of response) {
                    users.push({ value: user.id, label: user.name });
                    const option = document.createElement("option");

                    option.value = user.id;
                    option.textContent = user.name;

                    users_select.appendChild(option);
                }

                fields.user_id.options = users;

                FormBuilder();
            } catch (error) {
                console.log(error);
            }
        }

        getUsers();
        users_select.addEventListener("change", function (event) {
            form_field_values.user_id = event.target.value;
            FormBuilder();
            getPosts({ user_id: event.target.value });
        });

        // if (refresh_button) {
        //   refresh_button.addEventListener("click", getPosts);
        // }

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
                            td.addEventListener("click", function (event) {
                                form_field_values.id = row.id;
                                form_field_values.user_id = row.user_id;
                                form_field_values.title = row.title;
                                form_field_values.body = row.body;
                                FormBuilder();
                                modal.show();
                            });
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
