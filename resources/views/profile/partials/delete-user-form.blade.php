<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold text-danger mb-2">
            Excluir Conta
        </h2>
        <p class="text-muted small">
            Após a exclusão da sua conta, todos os seus recursos e dados serão permanentemente excluídos. 
            Antes de excluir sua conta, faça o download de todos os dados ou informações que deseja manter.
        </p>
    </header>

    <button type="button" 
            class="btn btn-danger" 
            data-bs-toggle="modal" 
            data-bs-target="#confirmUserDeletion">
        Excluir Conta
    </button>

    {{-- Modal de Confirmação --}}
    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUserDeletionLabel">
                            Tem certeza de que deseja excluir sua conta?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Após a exclusão da sua conta, todos os seus recursos e dados serão permanentemente excluídos. 
                            Por favor, digite sua senha para confirmar que deseja excluir permanentemente sua conta.
                        </p>

                        <div class="mb-3">
                            <label for="password" class="visually-hidden">Senha</label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                                   placeholder="Senha">
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger">
                            Excluir Conta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Auto-abrir modal se houver erros --}}
    @if($errors->userDeletion->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('confirmUserDeletion'));
                modal.show();
            });
        </script>
    @endif
</section>
