/**
 * @file cypress/tests/integration/Statistics.spec.js
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('Versioning tests', function() {

	// it('Editor can edit publication details', function() {
		// cy.findSubmissionAsEditor('dbarnes', null, 'Signalling Theory Dividends');
		// cy.login('dbarnes');
		// cy.visit('/index.php/publicknowledge/workflow/access/9');
		// cy.get('#publication-button').click();

		// Title and abstract
		// cy.get('input[name=prefix-en_US]').type('The', {delay: 0});
		// cy.get('input[name=subtitle-en_US]').type('A Review Of The Literature And Empirical Evidence', {delay: 0});
		// cy.get('input[name=title-en_US]').clear();
		// const abstract = 'The signaling theory suggests that dividends signal future prospects of a firm. However, recent empirical evidence from the US and the Uk does not offer a conclusive evidence on this issue. There are conflicting policy implications among financial economists so much that there is no practical dividend policy guidance to management, existing and potential investors in shareholding. Since corporate investment, financing and distribution decisions are a continuous function of management, the dividend decisions seem to rely on intuitive evaluation.';
		// cy.setTinyMceContent('titleAbstract-abstract-control-en_US', abstract.repeat(10));
		// cy.get('#titleAbstract-abstract-control-en_US').click(); // Ensure blur event is fired
		// cy.get('input[name=subtitle-en_US]').click();
		// cy.get('#titleAbstract button').contains('Save').click();

		// cy.get('[id*=title-error-en_US]').find('span').contains('You must complete this field in English.');
		// cy.get('[id*=abstract-error-en_US]').find('span').contains('The abstract is too long.');
		// cy.get('input[name=title-en_US').type('Signalling Theory Dividends', {delay: 0});
		// cy.setTinyMceContent('titleAbstract-abstract-control-en_US', abstract);
		// cy.get('#titleAbstract-abstract-control-en_US').click(); // Ensure blur event is fired
		// cy.get('input[name=subtitle-en_US]').click();
		// cy.get('#titleAbstract button').contains('Save').click();

		// cy.contains('The title and abstract have been updated.');

		// Metadata
		// cy.get('#metadata-button').click();
		// cy.get('#metadata-keywords-control-en_US').type('pr', {delay: 0});
		// cy.get('li').contains('Professional Development').click({force: true});
		// cy.get('#metadata-keywords-control-en_US').type('social{downarrow}{downarrow}{enter}', {delay: 0});
		// cy.get('#metadata button').contains('Save').click();

		// cy.contains('The metadata have been updated.');
		// cy.get('#metadata-keywords-selected-en_US').contains('Professional Development');
		// cy.get('#metadata-keywords-selected-en_US').contains('Social Transformation');

		// Permissions & Disclosure (sanity check only)
		// cy.get('#license-button').click();
		// cy.get('#license button').contains('Save').click();
		// cy.contains('The copyright and license information have been updated.');

		// Issue
		// cy.get('#issue-button').click();
		// cy.get('[name="sectionId"]').select('Reviews');
		// cy.get('[name="sectionId"]').select('Articles');
		// cy.get('[name="pages"]').type('71-98', {delay: 0});
		// cy.get('[name="urlPath"]').type('mwandenga-signalling-theory space error');
		// cy.get('#issue button').contains('Save').click();

		// cy.get('[id*="urlPath-error"]').contains('This may only contain letters, numbers, dashes and underscores.');
		// cy.get('[name="urlPath"]').type('mwandenga-signalling-theory');
		// cy.get('#issue button').contains('Save').click();

		// cy.contains('The publication\'s issue details have been updated.');

		// Contributors
		// cy.get('#contributors-button').click();
		// cy.get('[id*="authorgrid-addAuthor-button"]').click();
		// cy.get('[name="givenName[en_US]"]').type('Lorem', {delay: 0});
		// cy.get('[name="familyName[en_US]"]').type('Ipsum', {delay: 0});
		// cy.get('[name="email"]').type('lorem@mailinator.com', {delay: 0});
		// cy.get('[name="country"]').select('South Africa');
		// cy.get('label').contains('Author').click();
		// cy.get('[id^="submitFormButton"]').contains('Save').click();
		// cy.contains('Author added.');
		// cy.get('[id*="authorgrid-row"]').contains('Lorem Ipsum');

		// Create a galley
		// cy.get('button#publication-button').click();
		// cy.get('button#galleys-button').click();
		// cy.get('a[id^="component-grid-articlegalleys-articlegalleygrid-addGalley-button-"]').click();
		// cy.wait(1000); // Wait for the form to settle
		// cy.get('input[id^=label-]').type('PDF', {delay: 0});
		// cy.get('form#articleGalleyForm button:contains("Save")').click();
		// cy.get('select[id=genreId]').select('Article Text');
		// cy.wait(250);
		// cy.fixture('dummy.pdf', 'base64').then(fileContent => {
		// 	cy.get('div[id^="fileUploadWizard"] input[type=file]').upload(
		// 		{fileContent, 'fileName': 'article.pdf', 'mimeType': 'application/pdf', 'encoding': 'base64'}
		// 	);
		// });
		// cy.get('button').contains('Continue').click();
		// cy.get('button').contains('Continue').click();
		// cy.get('button').contains('Complete').click();
	// });

	// it('Author can not edit publication details', function() {
	// 	cy.login('jmwandenga');
	// 	cy.visit('/index.php/publicknowledge/submissions');
	// 	cy.contains('Signalling Theory Dividends').parent().parent().click();
	// 	cy.get('#publication-button').click();
	// 	cy.get('#titleAbstract button').contains('Save').should('be.disabled');

	// 	cy.get('#contributors-button').click();
	// 	cy.get('[id*="authorgrid-addAuthor-button"]').should('not.exist');
	// 	cy.get('[id*="editAuthor-button"]').should('not.exist');

	// 	cy.get('#galleys-button').click();
	// 	cy.get('[id*="addGalley-button"]').should('not.exist');
	// 	cy.get('[id*="editGalley-button"]').should('not.exist');
	// });

	// it('Allow author to edit publication details', function() {
	// 	cy.findSubmissionAsEditor('dbarnes', null, 'Signalling Theory Dividends');
	// 	cy.get('#stageParticipantGridContainer .label').contains('John Mwandenga')
	// 		.parent().parent().find('.show_extras').click()
	// 		.parent().parent().siblings().find('a').contains('Edit').click();
	// 	cy.get('[name="canChangeMetadata"]').check();
	// 	cy.get('[id^="submitFormButton"]').contains('OK').click();
	// 	cy.contains('The stage assignment has been changed.');
	// 	cy.logout();

	// 	cy.login('jmwandenga');
	// 	cy.visit('/index.php/publicknowledge/submissions');
	// 	cy.contains('Signalling Theory Dividends').parent().parent().click();
	// 	cy.get('#publication-button').click();
	// 	cy.get('#titleAbstract button').contains('Save').click();
	// 	cy.contains('The title and abstract have been updated.');
	// });

	it('Publish submission', function() {
		cy.findSubmissionAsEditor('dbarnes', null, 'Signalling Theory Dividends');
		cy.publish('1', 'Vol. 1 No. 2 (2014)');
		cy.isInIssue(title, 'Vol. 1 No. 2 (2014)');
	});

	it('Editor must create version to make changes', function() {
		cy.findSubmissionAsEditor('dbarnes', null, 'Signalling Theory Dividends');
		cy.get('#publication-button').click();
		cy.get('#titleAbstract button').contains('Save').should('be.disabled');
		cy.get('#publication button').contains('Create New Version').click();
		cy.contains('Are you sure you want to create a new version?');
		cy.get('button').contains('Yes').click();

		// Toggle between versions
		cy.get('#publication button').contains('All Versions').click();
		cy.get('.pkpPublication__versions .pkpDropdown__action').contains('1').click();
		cy.contains('This version has been published and can not be edited.');
		cy.get('#titleAbstract button').contains('Save').should('be.disabled');
		cy.get('#publication button').contains('All Versions').click();
		cy.get('.pkpPublication__versions .pkpDropdown__action').contains('2').click();
		cy.get('#publication button').contains('Publish');
		cy.contains('This version has been published and can not be edited.').should('not.exist');

		// Edit unpublished version's title
		cy.get('input[name=title-en_US').type(' Version 2', {delay: 0});
		cy.get('#titleAbstract button').contains('Save').click();
		cy.contains('The title and abstract have been updated.');

		// Edit Contributor
		cy.get('#contributors-button').click();
		cy.contains('Add Contributor');
		cy.get('[id*="editAuthor-button"]').click();
		cy.get('[name="familyName[en_US]"]').type('Version 2', {delay: 0});
		cy.get('[id^="submitFormButton"]').contains('Save').click();
		cy.contains('Author edited.');
		cy.get('[id*="authorgrid-row"]').contains('John Mwandenga Version 2');

		// Edit Galley
		cy.get('#galleys-button').click();
		cy.contains('Add Galley');
		cy.get('[id*="editGalley-button"]').click();
		cy.get('[name="label"]').type(' Version 2');
		cy.get('#representationsGrid [id*="downloadFile-button"').contains('PDF Version 2');



	});

	it('Article landing page displays versions', function() {

	});

	it('Article landing page displays correct version after version is unpublished', function() {

	});

	it('Article and galley URLs are correct when pub ids change between versions', function() {

	});

	it('Recommend-only editors can not publish, unpublish or create versions', function() {

	});
});
