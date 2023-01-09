@extends('layouts/blankLayout')

@section('title', 'POSTS - Pages')

@section('page-style')
<!-- Page -->
<!-- <link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-misc.css')}}"> -->
@endsection

@section('page-script')
<script >
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
  })

    var user_id = "";

    const base_url = "https://gorest.co.in/public/v2";

    function FormBuilder(){
      form.innerHTML = "";
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

      if(form_field_values[key]) {
        input.value = form_field_values[key];
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
      form_field_values.user_id = event.target.value;
      FormBuilder();
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

</script>
@endsection

@section('content')
<!--Under Maintenance -->
<div class="container-xxl container-p-y">
  <div class="misc-wrapper">
    <h2 class="mb-2 mx-2">POSTS!</h2>

    <div class="carregando d-none">Carregando...</div>
    <div class="d-flex flex-row gap-2 align-items-center">

      <select name="users" id="users" class="users_select form-select">
        <option value="">Select user</option>
      </select>
      <!-- <button type="button" id="load-posts" class="btn btn-primary tx-nowrap">Carregar Posts</button> -->
      <button type="button" id="load-posts" class="btn btn-primary nowrap" data-bs-toggle="modal" data-bs-target=".post-form-modal">NOVO</button>
    </div>
    <table class="table table-border">
      <thead>
        <tr></tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<section class="post-form-modal modal fade">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar POST</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="post_form" class="row g-3"></form>
      </div>
    </div>
  </div>
</section>
<!-- /Under Maintenance -->
@endsection
