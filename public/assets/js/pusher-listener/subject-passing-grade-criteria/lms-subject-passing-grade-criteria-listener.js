document.addEventListener('DOMContentLoaded', function () {
    window.Echo.channel('subjectPassingGradeCriteria')
        .listen('.lms.subject.passing.grade.criteria', (event) => {
            paginateSubjectPassingGradeCriteria(currentSearchYear, currentSearchClass, currentPage);
        });
});
