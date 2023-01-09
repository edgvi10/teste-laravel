@extends('layouts/blankLayout')

@section('title', 'POSTS - Pages')

@section('page-style')
    <!-- Page -->
    <!-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-misc.css') }}"> -->
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function(e) {
            (function() {
                const base_url = "https://gorest.co.in/public/v2";

                var user_id = "";

                // TOAST
                const toast_element = document.querySelector(".loading-toast");
                const loading_toast = new bootstrap.Toast(toast_element);

                // modal
                const post_form_modal_element = document.querySelector(".post-form-modal");
                const post_form_modal = new bootstrap.Modal(post_form_modal_element, {
                    backdrop: "static"
                });

                post_form_modal_element.addEventListener('show.bs.modal', function(event) {
                    if (form_field_values.id) post_form_modal_element.querySelector(".modal-title").textContent = "Edit Post";
                    else post_form_modal_element.querySelector(".modal-title").textContent = "New Post";
                    FormBuilder();
                });

                post_form_modal_element.addEventListener('hidden.bs.modal', function(event) {
                    form_field_values.id = "";
                    form_field_values.user_id = "";
                    form_field_values.title = "";
                    form_field_values.body = "";
                    FormBuilder();
                });

                const post_form_element = document.querySelector("#post-form");
                post_form_element.addEventListener("submit", function(event) {
                    event.preventDefault();
                    const form_data = new FormData(event.target);
                    const data = {};
                    for (const [key, value] of form_data.entries()) {
                        data[key] = value;
                    }

                    toast_element.querySelector(".toast-body").textContent = "Saving...";
                    loading_toast.show();

                    document.querySelector(".post-form-modal .btn-save").setAttribute("disabled", true);

                    setTimeout(() => {
                      loading_toast.hide();
                      post_form_modal.hide();
                      document.querySelector(".post-form-modal .btn-save").removeAttribute("disabled");
                    }, 5000);
                });

                var fields = {
                    id: {
                        label: "ID",
                        type: "input",
                        readonly: true,
                        required: true,
                        placeholder: "autoincrement"
                    },
                    user_id: {
                        label: "User ID",
                        type: "select",
                        size: "col-md-6",
                        required: true,
                        placeholder: "Autoincrement",
                        options: []
                    },
                    title: {
                        label: "Title",
                        size: "col-md-8",
                        type: "input",
                        required: false,
                        placeholder: "Title"
                    },
                    body: {
                        label: "Body",
                        size: "col-md-12",
                        type: "textarea",
                        required: false,
                        placeholder: "Body"
                    },
                }
                const form_field_values = {};

                const users_select = document.querySelector(".users_select");

                function FormBuilder() {
                    const form = post_form_element.querySelector("fieldset");
                    form.innerHTML = "";

                    for (const key in fields) {
                        const field = fields[key];
                        const div = document.createElement("div");
                        div.classList.add(field.size ?? "col-md-3");
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

                        if (form_field_values[key]) input.value = form_field_values[key];

                        if (field.options) {
                            const option_default = document.createElement("option");
                            option_default.value = "";
                            option_default.textContent = "Select";

                            input.appendChild(option_default);

                            for (const option of field.options) {
                                const option_html = document.createElement("option");
                                option_html.value = option.value;
                                option_html.textContent = option.label;
                                if (form_field_values[key] == option.value) option_html.setAttribute("selected", true);
                                input.appendChild(option_html);
                            }
                        }

                        if (field.type === "textarea") input.setAttribute("rows", 6);
                        if (field.readonly) input.setAttribute("readonly", true);
                        if (field.required) input.setAttribute("required", true);

                        div.appendChild(input);
                        form.appendChild(div);
                    }
                }

                async function getUsers(filter, order) {
                    try {
                        const request = await fetch(`${base_url}/users`);
                        const response = await request.json();

                        let users = [];
                        for (const user of response) {
                            users.push({
                                value: user.id,
                                label: user.name
                            });
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
                users_select.addEventListener("change", function(event) {
                    user_id = event.target.value;
                    form_field_values.user_id = event.target.value;
                    FormBuilder();
                    getPosts({
                        user_id: event.target.value
                    });
                });

                async function getPosts(filter = {}, order) {
                  toast_element.querySelector(".toast-body").textContent = "Loading...";
                  if(loading_toast) loading_toast.show();
                    try {

                        const url = (filter.user_id) ? `${base_url}/users/${filter.user_id}/posts` : `${base_url}/posts`;
                        const request = await fetch(url);
                        const response = await request.json();

                        const thead = document.querySelector("thead tr");
                        const tbody = document.querySelector("tbody");

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
                                    td.addEventListener("click", function(event) {
                                        form_field_values.id = row.id;
                                        form_field_values.user_id = row.user_id;
                                        form_field_values.title = row.title;
                                        form_field_values.body = row.body;
                                        FormBuilder();

                                        if(post_form_modal) post_form_modal.show();
                                    });
                                    tr.appendChild(td);
                                }
                                tbody.appendChild(tr);
                            }
                        }

                    } catch (error) {
                        console.log(error);
                    } finally {
                      if(loading_toast) loading_toast.hide();
                    }
                }

                getPosts();
            })();
        });
    </script>
@endsection


@section('content')
<style>
  *::-webkit-scrollbar {
    width: 0.5em;
    height: 0.5em;
}
*::-webkit-scrollbar-track {
    background-color: #f1f1f1;
}
*::-webkit-scrollbar-thumb {
    background-color: #888;
}
*::-webkit-scrollbar-thumb:hover {
    background-color: #555;
}
</style>
    <!--Under Maintenance -->
    <div class="container-xxl container-p-y">

            <div class="d-flex flex-row gap-2 align-items-center">
                <select name="users" id="users" class="users_select form-select">
                    <option value="">Select user</option>
                </select>
                <!-- <button type="button" id="load-posts" class="btn btn-primary tx-nowrap">Carregar Posts</button> -->
                <button type="button" id="load-posts" class="btn btn-primary nowrap" data-bs-toggle="modal"
                    data-bs-target=".post-form-modal">NOVO</button>
            </div>
            <table class="table table-sm rounded border table-hover bg-white my-3">
                <thead>
                    <tr></tr>
                </thead>
                <tbody></tbody>
            </table>
    </div>

    <section class="post-form-modal modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <form id="post-form" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar POST</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <fieldset class="row g-3">

                    </fieldset>
                </div>
                <footer class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-save" id="save-post">Salvar</button>
                </footer>
            </form>
        </div>
    </section>
    <div class="toast fade bg-primary position-absolute top-0 end-0 m-2 loading-toast" role="alert" aria-live="assertive" aria-atomic="true" style="z-index:9999">
      <div class="toast-body"></div>
    </div>
    <!-- /Under Maintenance -->
@endsection
