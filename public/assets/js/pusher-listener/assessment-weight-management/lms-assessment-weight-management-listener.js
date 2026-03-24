document.addEventListener('DOMContentLoaded', function () {
    window.Echo.channel('lmsAssessmentWeight')
        .listen('.lms.assessment.weight', (event) => {
            assessmentWeightManagement();
        });
});
