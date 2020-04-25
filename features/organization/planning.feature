Feature:
    In order to know the availability of the resources in my organization,
    As an organization
    I must have access to the planning and i can filter what is displayed.

    Scenario: As an anonymous, i cannot access the planning
        Given I go to "/organizations/planning"
        Then I should be on "/organizations/login"
        And the response status code should be 200

    Scenario: As an organization, i have access to the planning and i can see my resources
        Given I am authenticated as "UL 01-02"
        And I am on "/organizations"
        When I follow "Afficher les disponibilités de mes bénévoles pour la semaine prochaine"
        Then the response status code should be 200
        And I should be on "/organizations/planning/"
        And I should see "VPSP - 75012"
        And I should see "VPSP - 75014"
        And I should see "VL - 75016"
        And I should see "VL - 75018"
        And I should not see "7599"
        And I should not see "7799"
        And I should not see "7710"
        And I should see "Jane DOE"
        And I should not see "John DOE"

    Scenario: As an organization, i have access to the planning and i can see my resources
        Given I am authenticated as "UL 01-02"
        And I am on "/organizations/planning"
        Then the response status code should be 200
        And availability of user "Jane DOE" should be "unknown" on "next monday" at 02:00
        And availability of user "Jane DOE" should be "booked" on "next monday" at 06:00
        And availability of user "Jane DOE" should be "available" on "next monday" at 10:00
        And availability of user "Jane DOE" should be "locked" on "next monday" at 14:00
