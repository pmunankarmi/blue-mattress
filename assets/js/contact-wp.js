(() => {
  const form = document.getElementById('ctForm');
  if (!form) return;
  const company = form.querySelector('.blue-company-field');
  const companyInput = company?.querySelector('input');
  const update = () => {
    const business = form.querySelector('[name="audience"]:checked')?.value === 'business';
    company?.classList.toggle('is-visible', business);
    if (companyInput) companyInput.required = business;
  };
  form.querySelectorAll('[name="audience"]').forEach((input) => input.addEventListener('change', update));
  update();
})();

