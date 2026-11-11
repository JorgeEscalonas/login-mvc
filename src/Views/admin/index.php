<h1>Usuarios registrados</h1>

<p><a class="button-link button-primary" href="/admin/users">Administrar cuentas</a></p>

<div class="table-scroll">
    <table class="users-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user->id ?></td>
                    <td><?= $this->e($user->name) ?></td>
                    <td><?= $this->e($user->email) ?></td>
                    <td><?= $this->e($user->role->value) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="link"><a href="/dashboard">Volver al panel</a></p>