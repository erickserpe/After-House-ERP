// js/script.js

document.addEventListener('DOMContentLoaded', () => {
  // Validação dos formulários
  const forms = document.querySelectorAll('form.needs-validation');
  forms.forEach(form => {
    form.addEventListener('submit', e => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        alert('Por favor, preencha todos os campos obrigatórios corretamente.');
      }
      form.classList.add('was-validated');
    });
  });

  // Fornecedores, Produtos e Simulador são simples, mas Receitas tem parte dinâmica:
  // Função para adicionar uma nova linha de ingrediente na receita
  const addIngredBtn = document.getElementById('add-ingredient-btn');
  if (addIngredBtn) {
    addIngredBtn.addEventListener('click', () => {
      const container = document.getElementById('ingredients-container');
      const index = container.children.length;

      // Criar novo grupo de inputs para ingrediente
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

    // Delegação para remover ingrediente
    document.getElementById('ingredients-container').addEventListener('click', e => {
      if (e.target.classList.contains('remove-ingredient-btn')) {
        e.target.closest('div.row').remove();
      }
    });
  }

  // Simulador: cálculo dinâmico do total de bebidas baseado no número de pessoas e bebidas por pessoa
  const simuladorForm = document.getElementById('simulador-form');
  if (simuladorForm) {
    simuladorForm.addEventListener('submit', e => {
      e.preventDefault();

      const numPessoas = parseInt(document.getElementById('num_pessoas').value);
      const bebidasPorPessoa = parseFloat(document.getElementById('bebidas_por_pessoa').value);

      if (isNaN(numPessoas) || numPessoas <= 0 || isNaN(bebidasPorPessoa) || bebidasPorPessoa <= 0) {
        alert('Por favor, informe valores válidos para número de pessoas e bebidas por pessoa.');
        return;
      }

      const totalBebidas = numPessoas * bebidasPorPessoa;

      const resultado = document.getElementById('resultado');
      resultado.textContent = `Total estimado de bebidas: ${totalBebidas.toFixed(0)}`;
    });
  }

  // Feedback visual: esconder alertas automáticos após 4 segundos
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.display = 'none';
    }, 4000);
  });
});
