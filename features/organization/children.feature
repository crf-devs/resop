Feature:
    In order to manage my children organizations,
    As a parent organization,
    I must be able to list, edit and create them.

    Scenario: As anonymous, I cannot list the children of an organization
        When I go to "/organizations/children"
        Then I should be on "/organizations/login"
        And the response status code should be 200

    Scenario: As a parent organization, I can list the children of my organization
        Given I am authenticated as "DT75"
        When I go to "/organizations"
        Then I should see "Modifier mes structures"
        When I follow "Modifier mes structures"
        Then I should be on "/organizations/children"
        And the response status code should be 200
        And I should see "UL 01-02"

    Scenario: As an organization without children, I cannot list the children of my organization
        Given I am authenticated as "UL 01-02"
        When I go to "/organizations/203"
        Then I should not see "Modifier mes structures"
        When I go to "/organizations/children"
        And the response status code should be 403

    Scenario: As anonymous, I cannot create an organization
        When I go to "/organizations/new"
        Then I should be on "/organizations/login"
        And the response status code should be 200

    Scenario: As a parent organization, I can create an organization
        Given I am authenticated as "DT75"
        And I am on "/organizations/children"
        When I follow "Ajouter une structure"
        Then I should be on "/organizations/new"
        And the response status code should be 200
        When I fill in the following:
            | organization[name] | Lorem ipsum |
        And I press "Valider"
        Then the response status code should be 200
        And I should be on "/organizations/children"
        And I should see "La structure a été ajoutée avec succès."
        And I should see "Lorem ipsum"

    Scenario: As a children organization, I cannot create an organization
        Given I am authenticated as "UL 01-02"
        When I go to "/organizations/203"
        Then I should not see "Modifier mes structures"
        When I go to "/organizations/new"
        Then the response status code should be 403

    Scenario: As anonymous, I cannot update an organization
        When I go to "/organizations/201/edit"
        Then I should be on "/organizations/login"
        And the response status code should be 200

    Scenario: As a parent organization, I can update my children organizations
        Given I am authenticated as "DT75"
        And I am on "/organizations/children"
        When I follow "Modifier"
        Then I should be on "/organizations/203/edit"
        And the response status code should be 200
        And I should see "Modifier une structure"
        When I fill in the following:
            | organization[name] | Lorem ipsum |
        And I press "Valider"
        Then I should be on "/organizations/children"
        And the response status code should be 200
        And I should see "La structure a été mise à jour avec succès."
        And I should see "Lorem ipsum"

    Scenario: As an organization, I cannot update an organization I don't have access to
        Given I am authenticated as "DT75"
        When I go to "/organizations/202/edit"
        Then the response status code should be 403
