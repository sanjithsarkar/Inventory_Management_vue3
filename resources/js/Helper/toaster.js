
import { ElNotification } from 'element-plus';

export function useToastr() {
    return {
        success(message) {
            ElNotification({
                title: 'Success',
                message: message,
                type: 'success',
                position: 'bottom-right',
                duration: 3000
            });
        },
        
        error(message) {
            ElNotification({
                title: 'Error',
                message: message,
                type: 'error',
                position: 'bottom-right',
                duration: 3000
            });
        },
        
        info(message) {
            ElNotification({
                title: 'Info',
                message: message,
                type: 'info',
                position: 'bottom-right',
                duration: 3000
            });
        },
        
        warning(message) {
            ElNotification({
                title: 'Warning',
                message: message,
                type: 'warning',
                position: 'bottom-right',
                duration: 3000
            });
        }
    };
}
