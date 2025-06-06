import { ToastContainer, toast } from 'react-toastify';
import { __ } from '@wordpress/i18n';

import {
	comment,
	angry,
	sad,
	lame,
	smile,
	happy,
} from '../../static/icons/toast';
import { surveys } from '../../../constants';

const ToastContent = ( {
	action,
	category,
	eventData,
	heading,
	subheading,
	closeToast,
} ) => {
	return (
		<div
			className="nfd-survey-toast"
			data-survey-action={ action }
			data-survey-category={ category }
			data-survey-data={ eventData }
		>
			<div className="nfd-survey-toast__comment">
				<img
					className="nfd-survey-toast__comment__icon"
					src={ comment }
					alt={ __( 'Comment', 'wp-module-survey' ) }
				/>
			</div>
			<div className="nfd-survey-toast__content">
				<div className="nfd-survey-toast__content__heading">
					{ heading }
				</div>
				<div className="nfd-survey-toast__content__subheading">
					{ subheading }
				</div>
				<div
					className="nfd-survey-toast__content__buttons"
					onClick={ closeToast }
					onKeyDown={ closeToast }
					role="button"
					tabIndex={ -1 }
				>
					<img
						className="nfd-survey-toast__content__buttons--icon"
						src={ angry }
						alt={ __( 'Angry', 'wp-module-survey' ) }
						data-survey-option={ 1 }
					/>
					<img
						className="nfd-survey-toast__content__buttons--icon"
						src={ sad }
						alt={ __( 'Not Happy (Sad)', 'wp-module-survey' ) }
						data-survey-option={ 2 }
					/>
					<img
						className="nfd-survey-toast__content__buttons--icon"
						src={ lame }
						alt={ __( 'Lame', 'wp-module-survey' ) }
						data-survey-option={ 3 }
					/>
					<img
						className="nfd-survey-toast__content__buttons--icon"
						src={ smile }
						alt={ __( 'Smiley', 'wp-module-survey' ) }
						data-survey-option={ 4 }
					/>
					<img
						className="nfd-survey-toast__content__buttons--icon"
						src={ happy }
						alt={ __( 'Happy', 'wp-module-survey' ) }
						data-survey-option={ 5 }
					/>
				</div>
			</div>
		</div>
	);
};

const Toast = () => {
	surveys?.toast.forEach( ( survey ) => {
		toast(
			<ToastContent
				action={ survey.action }
				category={ survey.category }
				eventData={ JSON.stringify( survey.data ) }
				heading={ survey.heading }
				subheading={ survey.subheading }
			/>,
			{
				position: 'bottom-right',
			}
		);
	} );

	return <ToastContainer autoClose={ false } />;
};

export default Toast;
