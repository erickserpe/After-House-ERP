// js/script.js

document.addEventListener('DOMContentLoaded', () => {
  // Validação dos formulários Bootstrap 5 (se a classe 'needs-validation' for usada)
  const forms = document.querySelectorAll('form.needs-validation');
  forms.forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        // Opcional: pode adicionar um alerta geral ou focar no primeiro campo inválido
        // alert('Por favor, preencha todos os campos obrigatórios corretamente.');
      }
      form.classList.add('was-validated');
    }, false);
  });

  /* // INÍCIO: LÓGICA DE INGREDIENTES - RECOMENDADO REMOVER OU COMENTAR
  // Esta lógica é mais bem tratada pelo script específico em pages/receitas.php
  // que tem acesso direto aos dados dos produtos via PHP.

  const addIngredBtn = document.getElementById('add-ingredient-btn'); // ID diferente do usado em receitas.php
  if (addIngredBtn) {
    addIngredBtn.addEventListener('click', () => {
      const container = document.getElementById('ingredients-container');
      const index = container.children.length;

      // Criar novo grupo de inputs para ingrediente - esta estrutura é diferente da de receitas.php
      const newGroup = document.createElement('div');
      newGroup.className = 'mb-2 row align-items-center';

      newGroup.innerHTML = `
        <div class="col-5">
          <input type="text" name="ingredients[${index}][name]" class="form-control" placeholder="Ingrediente" required>
        </div>
        <div class="col-4">
          <input type="number" step="0.01" name="ingredients[${index}][quantity]" class="form-control" placeholder="Quantidade" required min="0.01">
        </div>
        <div class="col-2">
          <select name="ingredients[${index}][unit]" class="form-select" required>
            <option value="ml">ml</option>
            <option value="g">g</option>
            <option value="un">un</option>
          </select>
        </div>
        <div class="col-1">
          <button type="button" class="btn btn-danger btn-sm remove-ingredient-btn">&times;</button>
        </div>
      `;
      container.appendChild(newGroup);
    });

    // Delegação para remover ingrediente (se o container correto for usado)
    const ingredientsContainer = document.getElementById('ingredients-container');
    if (ingredientsContainer) {
        ingredientsContainer.addEventListener('click', e => {
            if (e.target && e.target.classList.contains('remove-ingredient-btn')) {
                e.target.closest('div.row.align-items-center').remove(); // Ajustar seletor
            }
        });
    }
  }
  // FIM: LÓGICA DE INGREDIENTES
  */


  // Simulador: cálculo dinâmico do total de bebidas baseado no número de pessoas e bebidas por pessoa
  // ESTA LÓGICA NÃO CORRESPONDE À FUNCIONALIDADE ATUAL DE pages/simulador.php
  // pages/simulador.php calcula o custo de ingredientes selecionados.
  const simuladorForm = document.getElementById('simulador-form'); // Este ID não existe em pages/simulador.php
  if (simuladorForm) {
    simuladorForm.addEventListener('submit', e => {
      e.preventDefault();

      const numPessoasEl = document.getElementById('num_pessoas'); // Não existe
      const bebidasPorPessoaEl = document.getElementById('bebidas_por_pessoa'); // Não existe
      const resultadoEl = document.getElementById('resultado'); // Não existe

      if (!numPessoasEl || !bebidasPorPessoaEl || !resultadoEl) {
          console.warn("Elementos do formulário do simulador (num_pessoas, bebidas_por_pessoa, resultado) não encontrados.");
          return;
      }

      const numPessoas = parseInt(numPessoasEl.value);
      const bebidasPorPessoa = parseFloat(bebidasPorPessoaEl.value);

      if (isNaN(numPessoas) || numPessoas <= 0 || isNaN(bebidasPorPessoa) || bebidasPorPessoa <= 0) {
        alert('Por favor, informe valores válidos para número de pessoas e bebidas por pessoa.');
        return;
      }

      const totalBebidas = numPessoas * bebidasPorPessoa;
      resultadoEl.textContent = `Total estimado de bebidas: ${totalBebidas.toFixed(0)}`;
    });
  }

  // Feedback visual: esconder alertas automáticos após 4 segundos
  const alerts = document.querySelectorAll('.alert'); // Seleciona todos os elementos com a classe .alert
  alerts.forEach(alert => {
    // Verifica se o alerta não é um alerta de erro persistente ou de sucesso que deva ser clicado
    if (!alert.classList.contains('alert-danger-persist') && !alert.classList.contains('alert-success-persist')) {
      setTimeout(() => {
        // Usar Bootstrap para fechar o alerta se ele for um componente Bootstrap
        const bsAlert = bootstrap.Alert.getInstance(alert);
        if (bsAlert) {
          bsAlert.close();
        } else {
          // Fallback para remover o elemento se não for um alerta Bootstrap gerenciável
          alert.style.display = 'none';
        }
      }, 4000); // 4 segundos
    }
  });
});