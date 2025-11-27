/**
 * Button Component
 * A customizable button with multiple variants following Apple design
 */

import React from 'react';
import {
  TouchableOpacity,
  TouchableOpacityProps,
  StyleSheet,
  ActivityIndicator,
  View,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';
import { ThemedText } from './ThemedText';

type ButtonVariant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'destructive';
type ButtonSize = 'sm' | 'md' | 'lg';

interface ButtonProps extends TouchableOpacityProps {
  title: string;
  variant?: ButtonVariant;
  size?: ButtonSize;
  loading?: boolean;
  disabled?: boolean;
  icon?: React.ReactNode;
  iconPosition?: 'left' | 'right';
  fullWidth?: boolean;
}

export function Button({
  title,
  variant = 'primary',
  size = 'md',
  loading = false,
  disabled = false,
  icon,
  iconPosition = 'left',
  fullWidth = false,
  style,
  ...props
}: ButtonProps) {
  const { colors, isDark } = useTheme();

  const isDisabled = disabled || loading;

  // Size configurations
  const sizeStyles = {
    sm: {
      paddingVertical: 8,
      paddingHorizontal: 16,
      borderRadius: Layout.borderRadius.sm,
      fontSize: Layout.fontSize.footnote,
    },
    md: {
      paddingVertical: 14,
      paddingHorizontal: 24,
      borderRadius: Layout.borderRadius.md,
      fontSize: Layout.fontSize.body,
    },
    lg: {
      paddingVertical: 18,
      paddingHorizontal: 32,
      borderRadius: Layout.borderRadius.lg,
      fontSize: Layout.fontSize.headline,
    },
  };

  // Variant configurations
  const getVariantStyles = () => {
    const baseStyles = {
      primary: {
        backgroundColor: isDisabled ? colors.textMuted : colors.primary,
        borderWidth: 0,
        borderColor: 'transparent',
        textColor: '#FFFFFF',
      },
      secondary: {
        backgroundColor: isDisabled ? colors.borderLight : colors.secondary,
        borderWidth: 0,
        borderColor: 'transparent',
        textColor: colors.text,
      },
      outline: {
        backgroundColor: 'transparent',
        borderWidth: 1.5,
        borderColor: isDisabled ? colors.textMuted : colors.primary,
        textColor: isDisabled ? colors.textMuted : colors.primary,
      },
      ghost: {
        backgroundColor: 'transparent',
        borderWidth: 0,
        borderColor: 'transparent',
        textColor: isDisabled ? colors.textMuted : colors.primary,
      },
      destructive: {
        backgroundColor: isDisabled ? colors.textMuted : colors.error,
        borderWidth: 0,
        borderColor: 'transparent',
        textColor: '#FFFFFF',
      },
    };
    return baseStyles[variant];
  };

  const variantStyles = getVariantStyles();
  const currentSizeStyles = sizeStyles[size];

  return (
    <TouchableOpacity
      style={[
        styles.button,
        {
          backgroundColor: variantStyles.backgroundColor,
          borderWidth: variantStyles.borderWidth,
          borderColor: variantStyles.borderColor,
          paddingVertical: currentSizeStyles.paddingVertical,
          paddingHorizontal: currentSizeStyles.paddingHorizontal,
          borderRadius: currentSizeStyles.borderRadius,
          opacity: isDisabled ? 0.7 : 1,
        },
        fullWidth && styles.fullWidth,
        style,
      ]}
      disabled={isDisabled}
      activeOpacity={0.7}
      {...props}
    >
      {loading ? (
        <ActivityIndicator 
          color={variantStyles.textColor} 
          size="small" 
        />
      ) : (
        <View style={styles.content}>
          {icon && iconPosition === 'left' && (
            <View style={styles.iconLeft}>{icon}</View>
          )}
          <ThemedText
            style={[
              styles.text,
              { 
                color: variantStyles.textColor,
                fontSize: currentSizeStyles.fontSize,
              },
            ]}
            weight="semibold"
          >
            {title}
          </ThemedText>
          {icon && iconPosition === 'right' && (
            <View style={styles.iconRight}>{icon}</View>
          )}
        </View>
      )}
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  button: {
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
  },
  fullWidth: {
    width: '100%',
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  text: {
    textAlign: 'center',
  },
  iconLeft: {
    marginRight: 8,
  },
  iconRight: {
    marginLeft: 8,
  },
});

export default Button;
