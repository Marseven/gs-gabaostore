import { test, expect } from '@playwright/test';

const ADMIN = { email: 'khal@gabaostore.ga', password: 'password' };
const OPERATEUR = { email: 'operateur@gabaostore.ga', password: 'password' };

async function login(page, { email, password }) {
    await page.goto('/login');
    await page.locator('input[type="email"]').fill(email);
    await page.locator('input[type="password"]').fill(password);
    await page.getByRole('button', { name: /Se connecter/ }).click();
    await expect(page.getByRole('heading', { name: 'Tableau de bord' })).toBeVisible();
}

async function selectArticle(page, ref) {
    const search = page.getByPlaceholder(/Rechercher un article/);
    await search.fill(ref);
    const option = page.getByRole('button', { name: new RegExp(ref) });
    await option.first().click();
}

test.describe('Parcours gestion de stock', () => {
    test('connexion admin et statistiques du dashboard', async ({ page }) => {
        await login(page, ADMIN);
        // 4 articles suivis, 1 en alerte (EL-002 stock 8 <= seuil 10)
        await expect(page.getByText('Articles suivis')).toBeVisible();
        await expect(page.getByText('EL-002')).toBeVisible();
    });

    test('création d’une entrée puis vérification du stock', async ({ page }) => {
        await login(page, ADMIN);
        await page.getByRole('link', { name: 'Saisie' }).click();

        await selectArticle(page, 'EL-002');
        await expect(page.getByText(/Stock disponible/)).toBeVisible();

        await page.locator('input[type="number"]').fill('5');
        await page.getByRole('button', { name: 'Enregistrer' }).click();
        await expect(page.getByText(/Entrée enregistrée/)).toBeVisible();

        // Vérifie le nouveau niveau sur la page Stock (8 + 5 = 13)
        await page.getByRole('link', { name: 'Stock' }).click();
        const row = page.getByRole('row', { name: /EL-002/ });
        await expect(row).toContainText('13');
        await expect(row).toContainText('OK');
    });

    test('une sortie supérieure au stock est bloquée', async ({ page }) => {
        await login(page, ADMIN);
        await page.getByRole('link', { name: 'Saisie' }).click();

        await page.getByRole('button', { name: /Sortie/ }).click();
        await selectArticle(page, 'EL-001'); // stock 20
        await page.locator('input[type="number"]').fill('9999');

        await expect(page.getByText(/Stock insuffisant/)).toBeVisible();
        await expect(page.getByRole('button', { name: 'Enregistrer' })).toBeDisabled();
    });

    test('une sortie valide enregistre livreur et destination', async ({ page }) => {
        await login(page, ADMIN);
        await page.getByRole('link', { name: 'Saisie' }).click();

        await page.getByRole('button', { name: /Sortie/ }).click();
        await selectArticle(page, 'PL-001'); // stock 50
        await page.locator('input[type="number"]').fill('3');
        await page.getByPlaceholder('Nom du livreur').fill('Marc Livreur');
        await page.getByPlaceholder(/Lieu ou client/).fill('Client Libreville');
        await page.getByRole('button', { name: 'Enregistrer' }).click();

        await expect(page.getByText(/Sortie enregistrée/)).toBeVisible();

        await page.getByRole('link', { name: 'Historique' }).click();
        await expect(page.getByText('Marc Livreur')).toBeVisible();
        await expect(page.getByText('Client Libreville')).toBeVisible();
    });

    test('l’opérateur n’a pas accès aux écrans admin', async ({ page }) => {
        await login(page, OPERATEUR);
        await expect(page.getByRole('link', { name: 'Articles' })).toHaveCount(0);
        await expect(page.getByRole('link', { name: 'Utilisateurs' })).toHaveCount(0);

        // Accès direct redirigé vers le dashboard
        await page.goto('/articles');
        await expect(page.getByRole('heading', { name: 'Tableau de bord' })).toBeVisible();
    });
});
