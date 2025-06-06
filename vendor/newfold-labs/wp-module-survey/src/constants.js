export const SURVEYS_DIV = 'nfd-survey';
export const surveys = window.nfdSurveySurveys?.queue;
export const wpRestURL = window.nfdSurveyDataAttrListener?.restUrl;
export const dataRestRoute = 'newfold-data/v1';
export const eventsAPI = `${ wpRestURL }/${ dataRestRoute }/events`;
